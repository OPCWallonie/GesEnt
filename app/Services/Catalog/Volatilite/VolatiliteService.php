<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\CatalogPrixHistorique;
use App\Models\ParametresEntreprise;
use Illuminate\Support\Collection;

class VolatiliteService
{
    public function __construct(
        private IndicateursCalculateur $calculateur,
        private ClassificateurVolatilite $classificateur,
        private GroupeComparaisonResolver $groupeResolver,
    ) {}

    public function recalculerProduit(CatalogProduit $produit): void
    {
        $params = ParametresEntreprise::instance();
        if (! $params->volatilite_active) {
            return;
        }

        $historique = CatalogPrixHistorique::where('catalog_produit_id', $produit->id)
            ->orderBy('detected_at')
            ->get();

        $this->recalculerUnProduit($produit, $historique, $params);
    }

    public function recalculerProduits(Collection $produits): int
    {
        if ($produits->isEmpty()) return 0;

        $params = ParametresEntreprise::instance();
        if (! $params->volatilite_active) return 0;

        $ids = $produits->pluck('id')->all();

        // Précharger tout l'historique en une requête
        $toutHistorique = CatalogPrixHistorique::whereIn('catalog_produit_id', $ids)
            ->orderBy('detected_at')
            ->get()
            ->groupBy('catalog_produit_id');

        foreach ($produits as $produit) {
            $historique = $toutHistorique->get($produit->id, collect());
            $this->recalculerUnProduit($produit, $historique, $params);
        }

        return $produits->count();
    }

    public function recalculerTous(): int
    {
        $total = 0;
        CatalogProduit::chunk(200, function ($produits) use (&$total) {
            $total += $this->recalculerProduits($produits);
        });
        return $total;
    }

    public function compareurEan(string $ean): Collection
    {
        return CatalogProduit::where('ean', $ean)
            ->whereNotNull('volatilite_calculee_at')
            ->get();
    }

    private function recalculerUnProduit(
        CatalogProduit $produit,
        Collection $historique,
        ParametresEntreprise $params,
    ): void {
        $indicateurs = $this->calculateur->calculer($produit, $historique, $params);

        if (! $indicateurs->suffisant((int) $params->volatilite_min_changements_pour_classer)) {
            [$signalRelatif, $signalAbsolu] = $this->appliquerFlagManuel(
                $produit->volatilite_flag_manuel,
                false,
                false,
            );
            $this->persister($produit, [
                'volatilite_classe'            => 'insuffisant',
                'volatilite_amplitude_pct'     => null,
                'volatilite_tendance_pct'      => null,
                'volatilite_position_relative' => null,
                'volatilite_nb_changements'    => $indicateurs->nbChangements,
                'volatilite_signal_relatif'    => $signalRelatif,
                'volatilite_signal_absolu'     => $signalAbsolu,
                'volatilite_groupe_comparaison'=> null,
                'volatilite_calculee_at'       => now(),
            ]);
            return;
        }

        [$groupe, $groupeLabel] = $this->groupeResolver->resoudre($produit);

        $tendances = $groupe->pluck('volatilite_tendance_pct')
            ->map(fn($v) => $v !== null ? (float) $v : null)
            ->toArray();
        $tendanceMediane = $this->groupeResolver->mediane($tendances);

        $classification = $this->classificateur->classifier(
            $indicateurs, $tendanceMediane, $groupeLabel, $params
        );

        [$signalRelatif, $signalAbsolu] = $this->appliquerFlagManuel(
            $produit->volatilite_flag_manuel,
            $classification->signalRelatif,
            $classification->signalAbsolu,
        );

        $this->persister($produit, [
            'volatilite_classe'            => $classification->classe,
            'volatilite_amplitude_pct'     => $indicateurs->amplitudePct,
            'volatilite_tendance_pct'      => $indicateurs->tendance12mPct,
            'volatilite_position_relative' => $indicateurs->positionRelative,
            'volatilite_nb_changements'    => $indicateurs->nbChangements,
            'volatilite_signal_relatif'    => $signalRelatif,
            'volatilite_signal_absolu'     => $signalAbsolu,
            'volatilite_groupe_comparaison'=> $classification->groupeComparaison,
            'volatilite_calculee_at'       => now(),
        ]);
    }

    private function appliquerFlagManuel(?string $flag, bool $signalRelatif, bool $signalAbsolu): array
    {
        return match ($flag) {
            'toujours_alerter' => [true, true],
            'jamais_alerter'   => [false, false],
            default            => [$signalRelatif, $signalAbsolu],
        };
    }

    private function persister(CatalogProduit $produit, array $data): void
    {
        CatalogProduit::withoutTimestamps(function () use ($produit, $data) {
            $produit->update($data);
        });
    }
}
