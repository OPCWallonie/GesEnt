<x-app-layout>
    <x-slot name="header">Peppol — Tableau de bord</x-slot>
    <x-slot name="actions">
        @if($params->peppolActif())
        <a href="{{ route('factures.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Gérer les envois
        </a>
        @endif
    </x-slot>

    {{-- Statut de la configuration --}}
    <div class="mb-6 p-4 rounded-xl border {{ $params->peppolActif() ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200' }}">
        <div class="flex items-center gap-3 text-sm">
            <span class="w-3 h-3 rounded-full flex-shrink-0 {{ $params->peppolActif() ? 'bg-green-500' : 'bg-amber-500' }}"></span>
            <span class="font-semibold {{ $params->peppolActif() ? 'text-green-800' : 'text-amber-800' }}">
                Mode : {{ ucfirst(str_replace('_', ' ', $params->peppol_mode ?? 'désactivé')) }}
            </span>
            <span class="text-gray-400">·</span>
            <span class="text-gray-600">
                Provider : {{ ucfirst($params->peppol_provider ?? '—') }}
                · Environnement : {{ $params->peppol_environment ?? '—' }}
            </span>
            @hasrole('admin')
            <a href="{{ route('parametres.edit') }}#peppol" class="ml-auto text-xs text-blue-600 hover:underline">
                Modifier la configuration →
            </a>
            @endhasrole
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Factures envoyées</div>
            <div class="text-2xl font-bold text-indigo-700">{{ $facturesPeppol }}</div>
            <div class="text-xs text-gray-400 mt-1">sur {{ $facturesTotal }} factures</div>
            @if($facturesAEnvoyer > 0)
                <div class="text-xs text-amber-600 mt-1 font-medium">{{ $facturesAEnvoyer }} à envoyer</div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Avoirs envoyés</div>
            <div class="text-2xl font-bold text-indigo-700">{{ $avoirsPeppol }}</div>
            <div class="text-xs text-gray-400 mt-1">sur {{ $avoirsTotal }} avoirs</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Achats Peppol reçus</div>
            <div class="text-2xl font-bold text-green-700">{{ $achatsPeppol }}</div>
            <div class="text-xs text-gray-400 mt-1">
                + {{ $achatsOcr }} OCR · {{ $achatsManuel }} manuelles
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs text-gray-400 uppercase font-medium mb-1">Erreurs webhook</div>
            <div class="text-2xl font-bold {{ $webhookErrors > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $webhookErrors }}</div>
            <div class="text-xs text-gray-400 mt-1">
                @if($webhookErrors > 0) À vérifier @else Tout est OK @endif
            </div>
        </div>
    </div>

    {{-- Alerte clients sans TVA --}}
    @if($facturesSansTva > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
        <span class="font-semibold">{{ $facturesSansTva }} facture(s) ne peuvent pas être envoyées via Peppol</span>
        car le client n'a pas de numéro de TVA renseigné. Les factures B2C (particuliers) ne sont pas soumises à l'obligation Peppol.
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Dernières réceptions Peppol --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-700">Dernières factures reçues via Peppol</h3>
                <a href="{{ route('factures-achat.index') }}" class="text-xs text-blue-600 hover:underline">Voir tout →</a>
            </div>
            @forelse($dernieresReceptions as $fa)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50">
                <div>
                    <a href="{{ route('factures-achat.show', $fa) }}"
                       class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                        {{ $fa->numero }}
                    </a>
                    <span class="text-xs text-gray-400 ml-2">{{ $fa->fournisseur?->nom }}</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-800">
                        {{ number_format($fa->montant_ttc, 2, ',', ' ') }} €
                    </div>
                    <div class="text-xs text-gray-400">{{ $fa->peppol_recu_at?->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-gray-400">Aucune facture reçue via Peppol pour le moment.</p>
            @endforelse
        </div>

        {{-- Logs webhook --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Journal des webhooks</h3>
            </div>
            @forelse($webhookLogs as $log)
            <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-50 last:border-0 text-xs">
                <div class="flex items-center gap-2">
                    @if($log->status === 'processed')
                        <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                    @elseif($log->status === 'duplicate')
                        <span class="w-2 h-2 rounded-full bg-gray-400 flex-shrink-0"></span>
                    @elseif($log->status === 'failed')
                        <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    @endif
                    <span class="text-gray-700">{{ $log->event_type }}</span>
                    @if($log->document_id)
                        <span class="text-gray-400 font-mono">{{ Str::limit($log->document_id, 20) }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-right">
                    <span class="text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    @if($log->status === 'failed')
                        <span class="text-red-500" title="{{ $log->error_message }}">Erreur</span>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-gray-400">Aucun webhook reçu.</p>
            @endforelse
        </div>

    </div>
</x-app-layout>
