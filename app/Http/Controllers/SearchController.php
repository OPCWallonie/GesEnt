<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
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

        $results = [];

        Client::where('nom', 'like', "%{$q}%")
            ->where('actif', true)->limit(4)->get(['id', 'nom'])
            ->each(fn($c) => $results[] = [
                'type' => 'Client', 'icon' => 'users',
                'label' => $c->nom,
                'url'   => route('clients.show', $c),
            ]);

        Chantier::where('nom', 'like', "%{$q}%")
            ->limit(4)->get(['id', 'nom'])
            ->each(fn($c) => $results[] = [
                'type' => 'Chantier', 'icon' => 'building',
                'label' => $c->nom,
                'url'   => route('chantiers.show', $c),
            ]);

        Devis::where('numero', 'like', "%{$q}%")
            ->with('client:id,nom')->limit(4)->get(['id', 'numero', 'client_id'])
            ->each(fn($d) => $results[] = [
                'type' => 'Devis', 'icon' => 'document',
                'label' => $d->numero . ($d->client ? ' — ' . $d->client->nom : ''),
                'url'   => route('devis.show', $d),
            ]);

        BonCommande::where('numero', 'like', "%{$q}%")
            ->with('client:id,nom')->limit(4)->get(['id', 'numero', 'client_id'])
            ->each(fn($b) => $results[] = [
                'type' => 'Bon de commande', 'icon' => 'document',
                'label' => $b->numero . ($b->client ? ' — ' . $b->client->nom : ''),
                'url'   => route('bons-commande.show', $b),
            ]);

        Facture::where('numero', 'like', "%{$q}%")
            ->with('client:id,nom')->limit(4)->get(['id', 'numero', 'client_id'])
            ->each(fn($f) => $results[] = [
                'type' => 'Facture', 'icon' => 'currency',
                'label' => $f->numero . ($f->client ? ' — ' . $f->client->nom : ''),
                'url'   => route('factures.show', $f),
            ]);

        Fournisseur::where('nom', 'like', "%{$q}%")
            ->limit(3)->get(['id', 'nom'])
            ->each(fn($f) => $results[] = [
                'type' => 'Fournisseur', 'icon' => 'users',
                'label' => $f->nom,
                'url'   => route('fournisseurs.show', $f),
            ]);

        return response()->json($results);
    }
}
