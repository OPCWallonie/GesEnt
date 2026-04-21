<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\ParametresEntreprise;
use App\Services\Catalog\Volatilite\DTO\ClassificationDTO;
use App\Services\Catalog\Volatilite\DTO\IndicateursDTO;

class ClassificateurVolatilite
{
    public function classifier(
        IndicateursDTO $indicateurs,
        ?float $tendanceMedianeGroupe,
        string $groupeLabel,
        ParametresEntreprise $params,
    ): ClassificationDTO {
        $classe = $this->determinerClasse($indicateurs, $params);

        [$signalRelatif, $signalAbsolu] = $this->calculerSignaux(
            $indicateurs, $tendanceMedianeGroupe, $params
        );

        return new ClassificationDTO(
            classe:            $classe,
            signalRelatif:     $signalRelatif,
            signalAbsolu:      $signalAbsolu,
            groupeComparaison: $groupeLabel,
        );
    }

    private function determinerClasse(IndicateursDTO $i, ParametresEntreprise $p): string
    {
        if (!$i->suffisant((int) $p->volatilite_min_changements_pour_classer)) {
            return 'insuffisant';
        }

        if ($i->amplitudePct !== null && $i->amplitudePct < (float) $p->volatilite_seuil_stable_amplitude_pct) {
            return 'stable';
        }

        // Classe a : anomalie isolée récente sur fond historiquement stable
        $aGrandeVariationRecente = false;
        foreach ($i->variationsRecentes3m as $v) {
            if (abs($v['pct']) >= (float) $p->volatilite_seuil_a_variation_pct) {
                $aGrandeVariationRecente = true;
                break;
            }
        }
        if ($aGrandeVariationRecente && $i->nbChangementsAnciens <= (int) $p->volatilite_seuil_a_max_changements_anciens) {
            return 'a';
        }

        // Classe b : augmentation/baisse constante et régulière
        if (
            $i->tendance12mPct !== null
            && abs($i->tendance12mPct) >= (float) $p->volatilite_seuil_b_pente_annuelle_pct
            && $i->r2Tendance !== null
            && $i->r2Tendance >= (float) $p->volatilite_seuil_b_r2_min
        ) {
            return 'b';
        }

        // Classe c : yoyo structurel
        if (
            $i->nbChangements >= (int) $p->volatilite_seuil_c_nb_changements
            && $i->amplitudePct !== null
            && $i->amplitudePct >= (float) $p->volatilite_seuil_c_amplitude_pct
        ) {
            return 'c';
        }

        return 'stable';
    }

    private function calculerSignaux(IndicateursDTO $i, ?float $tendanceMediane, ParametresEntreprise $p): array
    {
        if ($i->tendance12mPct === null) {
            return [false, false];
        }

        $signalAbsolu = abs($i->tendance12mPct) >= (float) $p->volatilite_garde_fou_absolu_pct;

        $signalRelatif = false;
        if ($tendanceMediane !== null) {
            $ecart = abs($i->tendance12mPct - $tendanceMediane);
            $signalRelatif = $ecart >= (float) $p->volatilite_signal_relatif_ecart_pct;
        }

        return [$signalRelatif, $signalAbsolu];
    }
}
