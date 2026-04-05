<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\ParametresEntreprise;
use Illuminate\Support\Carbon;

/**
 * Export des journaux comptables pour logiciels belges.
 *
 * Formats supportés :
 *   - winbooks  : CSV journal de ventes/achats Winbooks Connect
 *   - exact     : CSV format Exact Online Belgique
 *   - bob       : CSV format BOB 50 / Sage Bob
 *   - horus     : CSV format Horus
 *   - coda      : CODA (format bancaire belge - extrait de compte structuré)
 *
 * Les codes de journaux et comptes généraux peuvent être adaptés selon
 * le plan comptable configuré avec l'expert-comptable.
 */
class ExportComptableService
{
    // Codes de journaux par défaut (adaptables via paramètres)
    private const JOURNAL_VENTES  = 'VEN';
    private const JOURNAL_ACHATS  = 'ACH';
    private const COMPTE_CLIENT   = '400000';
    private const COMPTE_FOURN    = '440000';
    private const COMPTES_TVA     = [0 => '451000', 6 => '451600', 12 => '451200', 21 => '451210'];
    private const COMPTE_VENTES   = '700000';
    private const COMPTE_ACHATS   = '600000';

    // ---------------------------------------------------------------
    // Journal des ventes
    // ---------------------------------------------------------------

    public function exportVentes(int $annee, int $mois = null, string $format = 'winbooks'): string
    {
        $query = Facture::with('client', 'lignes')
            ->whereYear('date_document', $annee)
            ->whereIn('statut', ['en_attente', 'envoyee', 'payee', 'en_retard']);

        if ($mois) {
            $query->whereMonth('date_document', $mois);
        }

        $factures = $query->orderBy('date_document')->get();

        return match ($format) {
            'exact'  => $this->exactVentes($factures),
            'bob'    => $this->bobVentes($factures),
            'horus'  => $this->horusVentes($factures),
            default  => $this->winbooksVentes($factures),
        };
    }

    public function exportAchats(int $annee, int $mois = null, string $format = 'winbooks'): string
    {
        $query = FactureAchat::with('fournisseur')
            ->whereYear('date_document', $annee);

        if ($mois) {
            $query->whereMonth('date_document', $mois);
        }

        $factures = $query->orderBy('date_document')->get();

        return match ($format) {
            'exact'  => $this->exactAchats($factures),
            'bob'    => $this->bobAchats($factures),
            'horus'  => $this->horusAchats($factures),
            default  => $this->winbooksAchats($factures),
        };
    }

    // ---------------------------------------------------------------
    // Winbooks Connect
    // ---------------------------------------------------------------

    private function winbooksVentes($factures): string
    {
        $rows = [];
        // En-tête Winbooks journal des ventes
        $rows[] = implode(';', [
            'JOURNAL', 'DOCNUMBER', 'DOCDATE', 'DUEDATE',
            'ACCOUNT', 'NAME', 'COMMENT', 'AMOUNTEUR',
            'VATAMOUNT', 'VATCODE', 'VATBASE',
        ]);

        foreach ($factures as $f) {
            $dateDoc = $f->date_document->format('Ymd');
            $dateEch = $f->date_echeance ? $f->date_echeance->format('Ymd') : $dateDoc;
            $nom     = $this->clean($f->client->nom);

            // Ligne client (débit)
            $rows[] = implode(';', [
                self::JOURNAL_VENTES,
                $f->numero,
                $dateDoc,
                $dateEch,
                self::COMPTE_CLIENT,
                $nom,
                $f->numero,
                number_format($f->montant_ttc, 2, '.', ''),
                '',
                '',
                '',
            ]);

            // Ligne ventes HT (crédit)
            $rows[] = implode(';', [
                self::JOURNAL_VENTES,
                $f->numero,
                $dateDoc,
                $dateEch,
                self::COMPTE_VENTES,
                $nom,
                $f->numero,
                '-' . number_format($f->montant_ht, 2, '.', ''),
                '',
                '',
                '',
            ]);

            // Lignes TVA par taux
            foreach ($this->totauxTvaFacture($f) as $taux => $montant) {
                $compte = self::COMPTES_TVA[(int)$taux] ?? self::COMPTES_TVA[21];
                $rows[] = implode(';', [
                    self::JOURNAL_VENTES,
                    $f->numero,
                    $dateDoc,
                    $dateEch,
                    $compte,
                    $nom,
                    "TVA {$taux}%",
                    '-' . number_format($montant, 2, '.', ''),
                    number_format($montant, 2, '.', ''),
                    "S{$taux}",
                    number_format($montant / ($taux / 100), 2, '.', ''),
                ]);
            }
        }

        return implode("\r\n", $rows);
    }

    private function winbooksAchats($factures): string
    {
        $rows = [];
        $rows[] = implode(';', [
            'JOURNAL', 'DOCNUMBER', 'DOCDATE', 'DUEDATE',
            'ACCOUNT', 'NAME', 'COMMENT', 'AMOUNTEUR',
        ]);

        foreach ($factures as $f) {
            $dateDoc  = $f->date_document->format('Ymd');
            $dateEch  = $f->date_echeance ? $f->date_echeance->format('Ymd') : $dateDoc;
            $nom      = $this->clean($f->fournisseur->nom ?? 'Fournisseur');
            $numero   = $f->numero_facture ?? $f->id;

            $rows[] = implode(';', [
                self::JOURNAL_ACHATS, $numero, $dateDoc, $dateEch,
                self::COMPTE_FOURN, $nom, $numero,
                '-' . number_format($f->montant_ttc, 2, '.', ''),
            ]);

            $rows[] = implode(';', [
                self::JOURNAL_ACHATS, $numero, $dateDoc, $dateEch,
                self::COMPTE_ACHATS, $nom, $numero,
                number_format($f->montant_ht, 2, '.', ''),
            ]);
        }

        return implode("\r\n", $rows);
    }

    // ---------------------------------------------------------------
    // Exact Online Belgique
    // ---------------------------------------------------------------

    private function exactVentes($factures): string
    {
        $rows = [];
        $rows[] = implode(',', [
            '"Journal"', '"Entry number"', '"Entry date"', '"Due date"',
            '"Account"', '"Description"', '"Net amount"', '"VAT amount"',
            '"VAT code"', '"Currency"',
        ]);

        foreach ($factures as $f) {
            $rows[] = implode(',', [
                '"' . self::JOURNAL_VENTES . '"',
                '"' . $f->numero . '"',
                '"' . $f->date_document->format('d/m/Y') . '"',
                '"' . ($f->date_echeance?->format('d/m/Y') ?? '') . '"',
                '"' . $this->clean($f->client->nom) . '"',
                '"' . $f->numero . '"',
                number_format($f->montant_ht, 2, '.', ''),
                number_format($f->montant_ttc - $f->montant_ht, 2, '.', ''),
                '"' . $this->exactVatCode($f) . '"',
                '"EUR"',
            ]);
        }

        return implode("\r\n", $rows);
    }

    private function exactAchats($factures): string
    {
        $rows = [];
        $rows[] = implode(',', [
            '"Journal"', '"Entry number"', '"Entry date"',
            '"Supplier"', '"Description"', '"Net amount"', '"VAT amount"', '"Currency"',
        ]);

        foreach ($factures as $f) {
            $rows[] = implode(',', [
                '"' . self::JOURNAL_ACHATS . '"',
                '"' . ($f->numero_facture ?? $f->id) . '"',
                '"' . $f->date_document->format('d/m/Y') . '"',
                '"' . $this->clean($f->fournisseur->nom ?? '') . '"',
                '"' . ($f->numero_facture ?? '') . '"',
                number_format($f->montant_ht, 2, '.', ''),
                number_format($f->montant_ttc - $f->montant_ht, 2, '.', ''),
                '"EUR"',
            ]);
        }

        return implode("\r\n", $rows);
    }

    // ---------------------------------------------------------------
    // BOB 50 / Sage Bob
    // ---------------------------------------------------------------

    private function bobVentes($factures): string
    {
        $rows = [];
        $rows[] = 'DBK;DOCNB;DATE;DBKTYPE;BOOKYEAR;PERIOD;ACCOUNT;VATBASE;VATAMOUNT;AMOUNTEUR;DUEDATE;NAME';

        foreach ($factures as $f) {
            $rows[] = implode(';', [
                self::JOURNAL_VENTES,
                $f->numero,
                $f->date_document->format('d/m/Y'),
                '0',
                $f->date_document->year,
                $f->date_document->month,
                self::COMPTE_CLIENT,
                number_format($f->montant_ht, 2, '.', ''),
                number_format($f->montant_ttc - $f->montant_ht, 2, '.', ''),
                number_format($f->montant_ttc, 2, '.', ''),
                $f->date_echeance ? $f->date_echeance->format('d/m/Y') : '',
                $this->clean($f->client->nom),
            ]);
        }

        return implode("\r\n", $rows);
    }

    private function bobAchats($factures): string
    {
        $rows = [];
        $rows[] = 'DBK;DOCNB;DATE;DBKTYPE;BOOKYEAR;PERIOD;ACCOUNT;VATBASE;VATAMOUNT;AMOUNTEUR;DUEDATE;NAME';

        foreach ($factures as $f) {
            $rows[] = implode(';', [
                self::JOURNAL_ACHATS,
                $f->numero_facture ?? $f->id,
                $f->date_document->format('d/m/Y'),
                '1',
                $f->date_document->year,
                $f->date_document->month,
                self::COMPTE_FOURN,
                number_format($f->montant_ht, 2, '.', ''),
                number_format($f->montant_ttc - $f->montant_ht, 2, '.', ''),
                number_format($f->montant_ttc, 2, '.', ''),
                $f->date_echeance ? $f->date_echeance->format('d/m/Y') : '',
                $this->clean($f->fournisseur->nom ?? ''),
            ]);
        }

        return implode("\r\n", $rows);
    }

    // ---------------------------------------------------------------
    // Horus
    // ---------------------------------------------------------------

    private function horusVentes($factures): string
    {
        $rows = [];
        $rows[] = '"No pièce";"Date";"Tiers";"Nom";"Libellé";"HT";"TVA";"TTC";"Échéance"';

        foreach ($factures as $f) {
            $rows[] = implode(';', [
                '"' . $f->numero . '"',
                '"' . $f->date_document->format('d/m/Y') . '"',
                '"' . ($f->client->numero_client ?? $f->client_id) . '"',
                '"' . $this->clean($f->client->nom) . '"',
                '"' . $f->numero . '"',
                number_format($f->montant_ht, 2, ',', ''),
                number_format($f->montant_ttc - $f->montant_ht, 2, ',', ''),
                number_format($f->montant_ttc, 2, ',', ''),
                '"' . ($f->date_echeance?->format('d/m/Y') ?? '') . '"',
            ]);
        }

        return implode("\r\n", $rows);
    }

    private function horusAchats($factures): string
    {
        $rows = [];
        $rows[] = '"No pièce";"Date";"Tiers";"Nom";"Libellé";"HT";"TVA";"TTC";"Échéance"';

        foreach ($factures as $f) {
            $rows[] = implode(';', [
                '"' . ($f->numero_facture ?? $f->id) . '"',
                '"' . $f->date_document->format('d/m/Y') . '"',
                '"' . ($f->fournisseur->numero_fournisseur ?? $f->fournisseur_id) . '"',
                '"' . $this->clean($f->fournisseur->nom ?? '') . '"',
                '"' . ($f->numero_facture ?? '') . '"',
                number_format($f->montant_ht, 2, ',', ''),
                number_format($f->montant_ttc - $f->montant_ht, 2, ',', ''),
                number_format($f->montant_ttc, 2, ',', ''),
                '"' . ($f->date_echeance?->format('d/m/Y') ?? '') . '"',
            ]);
        }

        return implode("\r\n", $rows);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function clean(string $val): string
    {
        return str_replace(['"', ';', "\n", "\r"], [' ', ' ', ' ', ''], $val);
    }

    private function exactVatCode(Facture $f): string
    {
        $lignes = $f->lignes ?? collect();
        $taux   = $lignes->where('est_section', false)->pluck('taux_tva')->unique()->first() ?? 21;
        return match ((int)$taux) {
            0  => 'V00', 6  => 'V06', 12 => 'V12', default => 'V21',
        };
    }

    private function totauxTvaFacture(Facture $f): array
    {
        $totaux = [];
        foreach ($f->lignes ?? [] as $l) {
            if ($l->est_section) continue;
            $taux = (string)(float)$l->taux_tva;
            $totaux[$taux] = ($totaux[$taux] ?? 0) + ($l->montant_ht * ($l->taux_tva / 100));
        }
        return $totaux;
    }
}
