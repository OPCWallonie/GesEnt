<x-app-layout>
    <x-slot name="header">Factures</x-slot>
    <x-slot name="actions">
        <a href="{{ route('export.factures') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
        @if(($peppolMode ?? 'desactive') !== 'desactive' && \App\Models\ParametresEntreprise::instance()->peppolActif())
        @php
            $nonEnvoyeesPeppol = \App\Models\Facture::whereNull('peppol_envoye_at')
                ->whereIn('statut', ['en_attente', 'envoyee', 'en_retard'])
                ->whereHas('client', fn($q) => $q->whereNotNull('numero_tva'))
                ->count();
        @endphp
        @if($nonEnvoyeesPeppol > 0)
        <form method="POST" action="{{ route('factures.envoyer-peppol-masse') }}"
              onsubmit="return confirm('Envoyer {{ $nonEnvoyeesPeppol }} facture(s) via Peppol ? Cela peut prendre quelques secondes.')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Envoyer {{ $nonEnvoyeesPeppol }} facture(s) via Peppol
            </button>
        </form>
        @endif
        @endif
        <a href="{{ route('factures.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle facture
        </a>
    </x-slot>

    @if(($peppolMode ?? 'desactive') === 'desactive')
    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-4 text-sm text-amber-800 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Pensez à exporter vos factures vers votre logiciel comptable pour l'envoi Peppol obligatoire.
        <a href="{{ route('export-comptable.index') }}" class="font-medium underline ml-1">Exporter →</a>
    </div>
    @endif

    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Numéro ou client…"
               class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
        <select name="statut" class="rounded-lg border-gray-300 shadow-sm text-sm">
            <option value="">Tous les statuts</option>
            @foreach(['brouillon' => 'Brouillons', 'en_attente' => 'En attente', 'envoyee' => 'Envoyée', 'payee' => 'Payée', 'en_retard' => 'En retard', 'archive' => 'Archivée'] as $val => $label)
                <option value="{{ $val }}" @selected(request('statut') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filtrer</button>
        @if(request('q') || request('statut'))
            <a href="{{ route('factures.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Effacer</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client / Chantier</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net à payer</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($factures as $facture)
                    <tr class="hover:bg-gray-50 {{ $facture->estEnRetard() ? 'bg-red-50' : '' }}">
                        <td class="px-5 py-4 font-mono text-sm">
                            @if($facture->estBrouillon())
                                <a href="{{ route('factures.show', $facture) }}" class="text-gray-500 italic hover:text-blue-600">
                                    [Brouillon #{{ $facture->id }}]
                                </a>
                            @else
                                <a href="{{ route('factures.show', $facture) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                    {{ $facture->numero }}
                                </a>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('clients.show', $facture->client) }}" class="text-gray-700 hover:text-blue-600">{{ $facture->client->nom }}</a>
                            @if($facture->chantier)
                                <div class="text-xs text-gray-400 mt-0.5">{{ $facture->chantier->nom }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $facture->date_document->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 {{ $facture->estEnRetard() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                            {{ $facture->date_echeance?->format('d/m/Y') ?? '—' }}
                            @if($facture->estEnRetard()) <span class="text-xs">(retard)</span> @endif
                        </td>
                        <td class="px-5 py-4">
                            <x-badge :statut="$facture->statut"/>
                            @if($facture->retenue_garantie_pct > 0 && !$facture->retenue_garantie_liberee_at)
                                <span class="ml-1 text-amber-500 text-xs" title="Retenue de garantie {{ $facture->retenue_garantie_pct }}% non libérée">&#128274;</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right font-semibold {{ $facture->estEnRetard() ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('factures.show', $facture) }}" class="text-gray-400 hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('factures.pdf', $facture) }}" target="_blank" class="text-gray-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            Aucune facture trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100">{{ $factures->links() }}</div>
    </div>
</x-app-layout>
