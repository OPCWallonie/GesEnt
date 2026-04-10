<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\ParametresEntreprise;
use App\Models\RelanceEtape;

class MailTemplateService
{
    /**
     * Resolve the email body for a given document type, substituting variables.
     *
     * @param  string  $type     devis | facture | bdc | relance
     * @param  mixed   $document The Eloquent model (Devis, Facture…)
     */
    public static function resoudre(string $type, mixed $document): string
    {
        $p       = ParametresEntreprise::instance();
        $champ   = 'mail_template_' . $type;
        $template = (!empty($p->$champ)) ? $p->$champ : self::defaut($type);

        $montant = '';
        if (isset($document->montant_ttc)) {
            $montant = number_format((float) $document->montant_ttc, 2, ',', ' ') . ' €';
        } elseif (isset($document->montant_net_a_payer)) {
            $montant = number_format((float) $document->montant_net_a_payer, 2, ',', ' ') . ' €';
        }

        $vars = [
            '{client}'     => $document->client?->nom ?? '',
            '{numero}'     => $document->numero ?? '',
            '{montant}'    => $montant,
            '{entreprise}' => $p->nom ?? '',
            '{echeance}'   => isset($document->date_echeance) ? ($document->date_echeance?->format('d/m/Y') ?? '') : '',
            '{validite}'   => isset($document->date_validite) ? ($document->date_validite?->format('d/m/Y') ?? '') : '',
        ];

        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Resolve sujet or corps_email for a relance etape, substituting all variables.
     *
     * @param  'sujet'|'corps_email'  $champ
     */
    public static function resoudreEtape(RelanceEtape $etape, Facture $facture, string $champ = 'corps_email'): string
    {
        $p          = ParametresEntreprise::instance();
        $template   = $etape->$champ ?? '';
        $joursRetard = $facture->date_echeance
            ? max(0, (int) $facture->date_echeance->diffInDays(now()))
            : 0;

        $soldeDu = number_format((float) ($facture->montant_net_a_payer ?? $facture->montant_ttc), 2, ',', ' ') . ' €';

        $vars = [
            '{client}'         => $facture->client?->nom ?? '',
            '{numero}'         => $facture->numero ?? '',
            '{montant}'        => $soldeDu,
            '{solde_du}'       => $soldeDu,
            '{entreprise}'     => $p->nom ?? '',
            '{jours_retard}'   => (string) $joursRetard,
            '{date_facture}'   => $facture->date_echeance?->format('d/m/Y') ?? '',
            '{date_rappel}'    => now()->format('d/m/Y'),
            '{chantier}'       => $facture->chantier?->nom ?? '',
            '{nb_relance}'     => (string) ($facture->nb_relances + 1),
            '{delai_paiement}' => (string) ($facture->delai_reglement ?? 30),
        ];

        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    private static function defaut(string $type): string
    {
        $p = ParametresEntreprise::instance();
        $ent = $p->nom ?? 'Notre entreprise';

        return match ($type) {
            'devis'   => "Bonjour,\n\nVeuillez trouver ci-joint notre devis {numero}.\n\nN'hésitez pas à nous contacter pour toute question.\n\nCordialement,\n{entreprise}",
            'facture' => "Bonjour,\n\nVeuillez trouver ci-joint notre facture {numero} d'un montant de {montant}.\n\nCordialement,\n{entreprise}",
            'bdc'     => "Bonjour,\n\nVeuillez trouver ci-joint notre bon de commande {numero}.\n\nCordialement,\n{entreprise}",
            'relance' => "Bonjour,\n\nSauf erreur de notre part, notre facture {numero} d'un montant de {montant} reste impayée à ce jour.\n\nNous vous remercions de bien vouloir régulariser cette situation dans les meilleurs délais.\n\nCordialement,\n{entreprise}",
            default   => '',
        };
    }
}
