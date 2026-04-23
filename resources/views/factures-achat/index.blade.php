<x-app-layout>
    <x-slot name="header">Factures d'achats</x-slot>
    <x-slot name="actions">
        <a href="{{ route('export.factures-achat') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
        <a href="{{ route('factures-achat.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle facture achat
        </a>
    </x-slot>

    @if($totalEnCours > 0)
        <div class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-xl flex items-center justify-between text-sm">
            <span class="text-orange-800 font-medium">Total à payer aux fournisseurs</span>
            <span class="text-orange-900 font-bold text-lg">{{ number_format($totalEnCours, 2, ',', ' ') }} €</span>
        </div>
    @endif

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="N°, fournisseur, référence…"
               class="flex-1 min-w-40 rounded-lg border-gray-300 shadow-sm text-sm">
        <select name="statut" class="rounded-lg border-gray-300 shadow-sm text-sm">
            <option value="">Tous statuts</option>
            <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
            <option value="payee" @selected(request('statut') === 'payee')>Payée</option>
        </select>
        <select name="categorie" class="rounded-lg border-gray-300 shadow-sm text-sm">
            <option value="">Toutes catégories</option>
            @foreach(\App\Models\FactureAchat::$categories as $val => $label)
                <option value="{{ $val }}" @selected(request('categorie') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="fournisseur_id" class="rounded-lg border-gray-300 shadow-sm text-sm">
            <option value="">Tous fournisseurs</option>
            @foreach($fournisseurs as $f)
                <option value="{{ $f->id }}" @selected(request('fournisseur_id') == $f->id)>{{ $f->nom }}</option>
            @endforeach
        </select>
        <select name="archives" class="rounded-lg border-gray-300 shadow-sm text-sm">
            <option value="exclude" {{ $filtreArchives === 'exclude' ? 'selected' : '' }}>Archivées : masquer</option>
            <option value="include" {{ $filtreArchives === 'include' ? 'selected' : '' }}>Archivées : inclure</option>
            <option value="only" {{ $filtreArchives === 'only' ? 'selected' : '' }}>Archivées uniquement ({{ $nbArchives }})</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filtrer</button>
        @if(request()->anyFilled(['q', 'statut', 'categorie', 'fournisseur_id']) || $filtreArchives !== 'exclude')
            <a href="{{ route('factures-achat.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Effacer</a>
        @endif
    </form>

    @if($filtreArchives === 'only')
        <div class="mb-4 p-3 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-600 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1.343 9.142A2 2 0 008.334 19h7.332a2 2 0 001.991-1.858L19 8M10 12h4"/></svg>
            Affichage des factures d'achat archivées uniquement — documents en lecture seule conservés à des fins légales.
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chantier</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($factures as $fa)
                    <tr class="hover:bg-gray-50 {{ $fa->statut === 'archive' ? 'opacity-60' : ($fa->estEnRetard() ? 'bg-red-50' : '') }}">
                        <td class="px-5 py-4">
                            <a href="{{ route('factures-achat.show', $fa) }}" class="font-mono font-medium text-gray-900 hover:text-blue-600">
                                {{ $fa->numero }}
                            </a>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                @if($fa->reference_fournisseur)
                                    <span class="text-xs text-gray-400">{{ $fa->reference_fournisseur }}</span>
                                @endif
                                @if($fa->peppol_source === 'peppol')
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded"
                                          title="Reçue via Peppol le {{ $fa->peppol_recu_at?->format('d/m/Y H:i') }}">Peppol</span>
                                @elseif($fa->peppol_source === 'ocr')
                                    <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">OCR</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-700">
                            <a href="{{ route('fournisseurs.show', $fa->fournisseur) }}" class="hover:text-blue-600">{{ $fa->fournisseur->nom }}</a>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $fa->date_document->format('d/m/Y') }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $fa->categorie === 'materiel' ? 'bg-blue-100 text-blue-800' :
                                   ($fa->categorie === 'sous_traitance' ? 'bg-purple-100 text-purple-800' :
                                   ($fa->categorie === 'frais_generaux' ? 'bg-gray-100 text-gray-700' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ $fa->label_categorie }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-600 text-xs">{{ $fa->chantier?->nom ?? '—' }}</td>
                        <td class="px-5 py-4 text-right font-semibold {{ $fa->estEnRetard() ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($fa->montant_ttc, 2, ',', ' ') }} €
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($fa->statut === 'archive')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Archivée</span>
                            @elseif($fa->statut === 'payee')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Payée</span>
                            @elseif($fa->estEnRetard())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">En retard</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">En attente</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('factures-achat.show', $fa) }}" class="text-gray-400 hover:text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">Aucune facture d'achat.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100">{{ $factures->links() }}</div>
    </div>
</x-app-layout>
