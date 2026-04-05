<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        // Échapper les caractères spéciaux LIKE pour éviter d'élargir la recherche
        $qSafe = str_replace(['%', '_'], ['\%', '\_'], $q);
        $like  = "%{$qSafe}%";

        $results = [];

        // Clients : nom, email, téléphone, GSM, code client, n° TVA
        Client::where(function ($query) use ($like) {
                $query->where('nom', 'like', $like)
                      ->orWhere('email', 'like', $like)
                      ->orWhere('telephone', 'like', $like)
                      ->orWhere('gsm', 'like', $like)
                      ->orWhere('code_client', 'like', $like)
                      ->orWhere('numero_tva', 'like', $like);
            })
            ->where('actif', true)
            ->limit(4)
            ->get(['id', 'nom', 'ville'])
            ->each(function ($c) use (&$results) {
                $results[] = [
                    'type'  => 'Client',
                    'icon'  => 'users',
                    'label' => $c->nom . ($c->ville ? " — {$c->ville}" : ''),
                    'url'   => route('clients.show', $c),
                ];
            });

        // Chantiers : nom, adresse, ville, code postal
        Chantier::where(function ($query) use ($like) {
                $query->where('nom', 'like', $like)
                      ->orWhere('adresse_chantier', 'like', $like)
                      ->orWhere('ville', 'like', $like)
                      ->orWhere('code_postal', 'like', $like);
            })
            ->with('client:id,nom')
            ->limit(4)
            ->get(['id', 'nom', 'client_id', 'ville'])
            ->each(function ($c) use (&$results) {
                $results[] = [
                    'type'  => 'Chantier',
                    'icon'  => 'building',
                    'label' => $c->nom
                        . ($c->ville ? " ({$c->ville})" : '')
                        . ($c->client ? " — {$c->client->nom}" : ''),
                    'url'   => route('chantiers.show', $c),
                ];
            });

        // Devis : numéro, nom client, désignation des lignes
        Devis::where(function ($query) use ($like) {
                $query->where('numero', 'like', $like)
                      ->orWhereHas('client', fn($q) => $q->where('nom', 'like', $like))
                      ->orWhereHas('lignes', fn($q) => $q->where('designation', 'like', $like));
            })
            ->with('client:id,nom')
            ->limit(4)
            ->get(['id', 'numero', 'client_id', 'montant_ttc'])
            ->each(function ($d) use (&$results) {
                $results[] = [
                    'type'  => 'Devis',
                    'icon'  => 'document',
                    'label' => $d->numero
                        . ($d->client ? ' — ' . $d->client->nom : '')
                        . ' (' . number_format($d->montant_ttc, 0, ',', ' ') . ' €)',
                    'url'   => route('devis.show', $d),
                ];
            });

        // Bons de commande : numéro, nom client, désignation des lignes
        BonCommande::where(function ($query) use ($like) {
                $query->where('numero', 'like', $like)
                      ->orWhereHas('client', fn($q) => $q->where('nom', 'like', $like))
                      ->orWhereHas('lignes', fn($q) => $q->where('designation', 'like', $like));
            })
            ->with('client:id,nom')
            ->limit(4)
            ->get(['id', 'numero', 'client_id', 'montant_ttc'])
            ->each(function ($b) use (&$results) {
                $results[] = [
                    'type'  => 'Bon de commande',
                    'icon'  => 'document',
                    'label' => $b->numero
                        . ($b->client ? ' — ' . $b->client->nom : '')
                        . ' (' . number_format($b->montant_ttc, 0, ',', ' ') . ' €)',
                    'url'   => route('bons-commande.show', $b),
                ];
            });

        // Factures : numéro, nom client, désignation des lignes
        Facture::where(function ($query) use ($like) {
                $query->where('numero', 'like', $like)
                      ->orWhereHas('client', fn($q) => $q->where('nom', 'like', $like))
                      ->orWhereHas('lignes', fn($q) => $q->where('designation', 'like', $like));
            })
            ->with('client:id,nom')
            ->limit(4)
            ->get(['id', 'numero', 'client_id', 'montant_ttc', 'statut'])
            ->each(function ($f) use (&$results) {
                $results[] = [
                    'type'  => 'Facture',
                    'icon'  => 'currency',
                    'label' => $f->numero
                        . ($f->client ? ' — ' . $f->client->nom : '')
                        . ' (' . number_format($f->montant_ttc, 0, ',', ' ') . ' €)',
                    'url'   => route('factures.show', $f),
                ];
            });

        // Factures d'achat : numéro, nom fournisseur
        FactureAchat::where(function ($query) use ($like) {
                $query->where('numero', 'like', $like)
                      ->orWhereHas('fournisseur', fn($q) => $q->where('nom', 'like', $like));
            })
            ->with('fournisseur:id,nom')
            ->limit(3)
            ->get(['id', 'numero', 'fournisseur_id', 'montant_ttc'])
            ->each(function ($fa) use (&$results) {
                $results[] = [
                    'type'  => 'Facture achat',
                    'icon'  => 'currency',
                    'label' => $fa->numero
                        . ($fa->fournisseur ? ' — ' . $fa->fournisseur->nom : '')
                        . ' (' . number_format($fa->montant_ttc, 0, ',', ' ') . ' €)',
                    'url'   => route('factures-achat.show', $fa),
                ];
            });

        // Fournisseurs : nom, email, n° TVA
        Fournisseur::where(function ($query) use ($like) {
                $query->where('nom', 'like', $like)
                      ->orWhere('email', 'like', $like)
                      ->orWhere('numero_tva', 'like', $like);
            })
            ->limit(3)
            ->get(['id', 'nom'])
            ->each(function ($f) use (&$results) {
                $results[] = [
                    'type'  => 'Fournisseur',
                    'icon'  => 'users',
                    'label' => $f->nom,
                    'url'   => route('fournisseurs.show', $f),
                ];
            });

        return response()->json($results);
    }
}
