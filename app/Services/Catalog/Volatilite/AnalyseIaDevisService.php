<?php

namespace App\Services\Catalog\Volatilite;

use App\Models\CatalogProduit;
use App\Models\Devis;
use App\Models\DevisAnalyseIa;
use App\Services\Catalog\Volatilite\DTO\AnalyseIaDevisDTO;
use App\Services\Ia\LlmClientService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyseIaDevisService
{
    public function __construct(
        private LlmClientService               $llmClient,
        private VolatiliteDocumentHelper       $volatiliteHelper,
        private BadgeVolatiliteService         $badgeService,
        private ComparaisonFournisseursService $comparaisonService,
    ) {}

    public function cacheValide(Devis $devis): ?DevisAnalyseIa
    {
        $cache = DevisAnalyseIa::where('devis_id', $devis->id)->first();
        if (! $cache) return null;

        [$hashLignes, $hashAlternatives, $produitsAEnjeu] = $this->calculerHashes($devis);

        if ($produitsAEnjeu->isEmpty()) return null;

        if ($cache->hash_lignes !== $hashLignes || $cache->hash_alternatives !== $hashAlternatives) {
            return null;
        }

        return $cache;
    }

    public function analyser(Devis $devis): ?DevisAnalyseIa
    {
        if ($existant = $this->cacheValide($devis)) {
            return $existant;
        }

        [$hashLignes, $hashAlternatives, $produitsAEnjeu, $alternativesParProduit] = $this->calculerHashes($devis);

        if ($produitsAEnjeu->isEmpty()) {
            return null;
        }

        $devis->loadMissing('chantier', 'lignes');

        $prompt   = $this->construirePrompt($devis, $produitsAEnjeu, $alternativesParProduit);
        $resultat = $this->llmClient->appeler($prompt);

        $analyseTab = $this->parserReponse($resultat['contenu']);
        $analyseDTO = AnalyseIaDevisDTO::fromArray($analyseTab);

        return DevisAnalyseIa::updateOrCreate(
            ['devis_id' => $devis->id],
            [
                'hash_lignes'        => $hashLignes,
                'hash_alternatives'  => $hashAlternatives,
                'provider'           => $resultat['provider'],
                'modele'             => $resultat['modele'],
                'payload_envoye'     => ['prompt' => $prompt],
                'reponse_brute'      => ['contenu' => $resultat['contenu']],
                'analyse'            => $analyseDTO->toArray(),
                'duree_ms'           => $resultat['duree_ms'],
                'cout_tokens_entree' => $resultat['tokens_entree'],
                'cout_tokens_sortie' => $resultat['tokens_sortie'],
                'genere_at'          => now(),
            ]
        );
    }

    public function invalider(Devis $devis): void
    {
        DevisAnalyseIa::where('devis_id', $devis->id)->delete();
    }

    /**
     * @return array{0: string, 1: string, 2: Collection, 3: array}
     */
    private function calculerHashes(Devis $devis): array
    {
        $devis->loadMissing('lignes');

        $helperData   = $this->volatiliteHelper->preparerPourDocument($devis);
        $badges       = $helperData['badgesParProduit'];
        $alternatives = $helperData['alternativesParProduit'];

        $idsAEnjeu = collect(array_keys($badges))
            ->merge(array_keys($alternatives))
            ->unique()
            ->values();

        $produitsAEnjeu = CatalogProduit::whereIn('id', $idsAEnjeu)->get();

        $hashLignes       = $this->hashLignes($devis, $idsAEnjeu->all());
        $hashAlternatives = $this->hashAlternatives($alternatives);

        return [$hashLignes, $hashAlternatives, $produitsAEnjeu, $alternatives];
    }

    private function hashLignes(Devis $devis, array $produitsIds): string
    {
        if (empty($produitsIds)) return hash('sha256', 'vide');

        $items = $devis->lignes
            ->whereIn('catalog_produit_id', $produitsIds)
            ->where('est_section', false)
            ->sortBy('id')
            ->map(fn($l) => [
                'id'            => $l->id,
                'produit_id'    => $l->catalog_produit_id,
                'quantite'      => (float) $l->quantite,
                'prix_unitaire' => (float) $l->prix_unitaire,
            ])
            ->values()
            ->all();

        return hash('sha256', json_encode($items, JSON_UNESCAPED_UNICODE));
    }

    private function hashAlternatives(array $alternativesParProduit): string
    {
        $items = [];
        foreach ($alternativesParProduit as $produitId => $alternatives) {
            foreach ($alternatives as $alt) {
                $items[] = [
                    'produit'     => $produitId,
                    'alternative' => $alt->produit->id,
                    'prix'        => (float) $alt->produit->prix_catalogue,
                    'score'       => round($alt->scoreComposite, 2),
                ];
            }
        }
        usort($items, fn($a, $b) => [$a['produit'], $a['alternative']] <=> [$b['produit'], $b['alternative']]);
        return hash('sha256', json_encode($items, JSON_UNESCAPED_UNICODE));
    }

    private function construirePrompt(Devis $devis, Collection $produits, array $alternativesParProduit): string
    {
        $chantier = $devis->chantier;
        $duree    = null;
        if ($chantier && $chantier->date_debut && $chantier->date_fin_prevue) {
            $duree = Carbon::parse($chantier->date_debut)
                ->diffInDays(Carbon::parse($chantier->date_fin_prevue));
        }

        $contexteChantier = $chantier ? [
            'nom'                => $chantier->nom,
            'date_debut'         => $chantier->date_debut?->toDateString(),
            'date_fin_prevue'    => $chantier->date_fin_prevue?->toDateString(),
            'duree_prevue_jours' => $duree,
        ] : null;

        $lignesData = [];
        foreach ($devis->lignes as $ligne) {
            if (! $ligne->catalog_produit_id) continue;
            if (! $produits->contains('id', $ligne->catalog_produit_id)) continue;

            $produit = $produits->firstWhere('id', $ligne->catalog_produit_id);
            $badge   = $this->badgeService->composer($produit);

            $ligneItem = [
                'catalog_produit_id' => $produit->id,
                'designation'        => $produit->designation,
                'fournisseur'        => $produit->fournisseur,
                'quantite'           => (float) $ligne->quantite,
                'prix_unitaire_eur'  => (float) $ligne->prix_unitaire,
                'montant_ht_eur'     => (float) $ligne->montant_ht,
                'volatilite'         => [
                    'classe'            => $produit->volatilite_classe,
                    'message'           => $badge->message,
                    'tendance_12m_pct'  => $produit->volatilite_tendance_pct !== null ? (float) $produit->volatilite_tendance_pct : null,
                    'position_relative' => $produit->volatilite_position_relative !== null ? (float) $produit->volatilite_position_relative : null,
                    'amplitude_pct'     => $produit->volatilite_amplitude_pct !== null ? (float) $produit->volatilite_amplitude_pct : null,
                    'signal_absolu'     => (bool) $produit->volatilite_signal_absolu,
                    'signal_relatif'    => (bool) $produit->volatilite_signal_relatif,
                ],
                'alternatives_ean' => [],
            ];

            $alts = $alternativesParProduit[$produit->id] ?? collect();
            foreach ($alts as $alt) {
                $ligneItem['alternatives_ean'][] = [
                    'fournisseur'      => $alt->produit->fournisseur,
                    'designation'      => $alt->produit->designation,
                    'prix_eur'         => (float) $alt->produit->prix_catalogue,
                    'ecart_prix_pct'   => round($alt->ecartPrixPct, 2),
                    'tendance_12m_pct' => $alt->tendance12mPct !== null ? round($alt->tendance12mPct, 2) : null,
                    'signaux_forts'    => array_values(array_filter([
                        $alt->signalPrixInferieur      ? 'prix_inferieur'      : null,
                        $alt->signalPositionInferieure ? 'position_inferieure' : null,
                        $alt->signalTendanceFavorable  ? 'tendance_favorable'  : null,
                    ])),
                ];
            }

            $lignesData[] = $ligneItem;
        }

        $contexte = json_encode([
            'chantier' => $contexteChantier,
            'devis'    => [
                'numero'     => $devis->numero,
                'date'       => $devis->date_document?->toDateString(),
                'montant_ht' => (float) $devis->montant_ht,
            ],
            'lignes_a_enjeu' => $lignesData,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
Tu es un assistant spécialisé dans l'aide à la décision pour entrepreneurs de construction belges. Tu analyses des lignes de devis où les prix des produits catalogue présentent des signaux de volatilité ou des alternatives fournisseurs avantageuses.

# Ta mission
Produire une synthèse actionnable pour l'entrepreneur, au format JSON strict défini ci-dessous. Ne retourne RIEN d'autre que le JSON — pas de texte d'introduction, pas de markdown, pas de backticks.

# Contexte
```json
{$contexte}
```

# Règles d'analyse
1. **Tu ne dois recommander une action que si elle est CONCRÈTEMENT utile.** N'invente pas de recommandations pour des produits qui n'en méritent pas.
2. Une ligne sans alternative EAN avantageuse et sans signal fort de volatilité peut être ignorée dans `recommandations`.
3. Pour un chantier long (>90 jours), le stockage anticipé d'un produit en hausse régulière est souvent pertinent. Pour un chantier court (<30 jours), moins.
4. Un produit en classe `c` (yoyo) avec position haute justifie de négocier ou reporter. Position basse = opportunité d'achat.
5. Quand une alternative EAN a un écart de prix ≥10%, `swap_fournisseur` est une action à privilégier.
6. `economie_estimee_eur` : calcule en multipliant la quantité de la ligne par l'écart de prix unitaire estimé. Si c'est incalculable (stockage spéculatif), mets `0`.

# Schéma JSON strict (obligatoire)
```json
{
  "synthese": "string, 1-2 phrases synthétiques sur l'ensemble du devis",
  "niveau_alerte": "info" | "attention" | "important",
  "recommandations": [
    {
      "catalog_produit_id": integer,
      "designation": "string",
      "action_suggeree": "stocker" | "reporter" | "swap_fournisseur" | "negocier" | "aucune",
      "justification": "string, 2-4 phrases",
      "economie_estimee_eur": number
    }
  ]
}
```

# Niveau d'alerte
- `info` : observations mineures, pas d'action urgente
- `attention` : certains produits demandent surveillance ou décision d'achat à court terme
- `important` : décisions significatives recommandées (swap de fournisseur, stockage urgent, renégociation client)

Rappel final : **réponds UNIQUEMENT avec le JSON valide**, conforme au schéma. Aucun texte avant ou après.
PROMPT;
    }

    private function parserReponse(string $contenu): array
    {
        $nettoye = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($contenu));

        if (preg_match('/\{.*\}/s', $nettoye, $matches)) {
            $nettoye = $matches[0];
        }

        $data = json_decode($nettoye, true);

        if (! is_array($data)) {
            throw new \RuntimeException("Réponse LLM non parsable en JSON : " . substr($contenu, 0, 200));
        }

        if (! isset($data['synthese'], $data['niveau_alerte'], $data['recommandations'])) {
            throw new \RuntimeException("Schéma JSON incomplet : " . substr($nettoye, 0, 200));
        }

        if (! in_array($data['niveau_alerte'], ['info', 'attention', 'important'], true)) {
            $data['niveau_alerte'] = 'info';
        }

        if (! is_array($data['recommandations'])) {
            $data['recommandations'] = [];
        }

        $actionsValides = ['stocker', 'reporter', 'swap_fournisseur', 'negocier', 'aucune'];
        foreach ($data['recommandations'] as &$reco) {
            if (! isset($reco['action_suggeree']) || ! in_array($reco['action_suggeree'], $actionsValides, true)) {
                $reco['action_suggeree'] = 'aucune';
            }
        }

        return $data;
    }
}
