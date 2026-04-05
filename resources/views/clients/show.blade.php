<x-app-layout>
    <x-slot name="header">{{ $client->nom }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('clients.edit', $client) }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
            Modifier
        </a>
        <a href="{{ route('devis.create', ['client_id' => $client->id]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            + Nouveau devis
        </a>
    </x-slot>

    <div class="grid grid-cols-3 gap-6">

        {{-- Infos client --}}
        <div class="col-span-3 lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Coordonnées</h3>
                <dl class="space-y-2 text-sm">
                    @if($client->statut_juridique)
                        <div><dt class="text-gray-400 text-xs">Statut</dt><dd class="text-gray-800">{{ $client->statut_juridique }}</dd></div>
                    @endif
                    @if($client->adresse)
                        <div><dt class="text-gray-400 text-xs">Adresse</dt>
                            <dd class="text-gray-800">{{ $client->adresse }}<br>{{ $client->code_postal }} {{ $client->ville }}<br>{{ $client->pays }}</dd>
                        </div>
                    @endif
                    @if($client->telephone)
                        <div><dt class="text-gray-400 text-xs">Tél.</dt><dd class="text-gray-800">{{ $client->telephone }}</dd></div>
                    @endif
                    @if($client->gsm)
                        <div><dt class="text-gray-400 text-xs">GSM</dt><dd class="text-gray-800">{{ $client->gsm }}</dd></div>
                    @endif
                    @if($client->email)
                        <div><dt class="text-gray-400 text-xs">Email</dt>
                            <dd><a href="mailto:{{ $client->email }}" class="text-blue-600 hover:underline">{{ $client->email }}</a></dd>
                        </div>
                    @endif
                    @if($client->numero_tva)
                        <div><dt class="text-gray-400 text-xs">N° TVA</dt><dd class="font-mono text-gray-800">{{ $client->numero_tva }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- CA Client --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Chiffre d'affaires</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">CA total TTC</dt>
                        <dd class="font-bold text-blue-700 text-base">{{ number_format($caTotalTtc, 2, ',', ' ') }} €</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Encaissé</dt>
                        <dd class="font-medium text-green-600">{{ number_format($caEncaisse, 2, ',', ' ') }} €</dd>
                    </div>
                    @if($enCours > 0)
                    <div class="flex justify-between">
                        <dt class="text-gray-400">En cours</dt>
                        <dd class="font-medium text-orange-600">{{ number_format($enCours, 2, ',', ' ') }} €</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Compteurs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Documents</h3>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $client->devis_count }}</div>
                        <div class="text-xs text-gray-400">Devis</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $client->bons_commande_count }}</div>
                        <div class="text-xs text-gray-400">BDC</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $client->factures_count }}</div>
                        <div class="text-xs text-gray-400">Factures</div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($client->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-5">
                    <h3 class="font-semibold text-yellow-800 mb-2 text-sm">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $client->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Activité --}}
        <div class="col-span-3 lg:col-span-2 space-y-6">

            {{-- Chantiers --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Chantiers</h3>
                    <a href="{{ route('chantiers.create', ['client_id' => $client->id]) }}"
                       class="text-sm text-blue-600 hover:underline">+ Nouveau</a>
                </div>
                @forelse($chantiers as $chantier)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <a href="{{ route('chantiers.show', $chantier) }}" class="font-medium text-sm text-gray-800 hover:text-blue-600">
                                {{ $chantier->nom }}
                            </a>
                            <span class="ml-2"><x-badge :statut="$chantier->statut"/></span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $chantier->devis_count }} devis</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucun chantier.</p>
                @endforelse
            </div>

            {{-- Derniers devis --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Derniers devis</h3>
                    <a href="{{ route('devis.index', ['client_id' => $client->id]) }}" class="text-sm text-blue-600 hover:underline">Voir tous</a>
                </div>
                @forelse($derniersDevis as $devis)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <a href="{{ route('devis.show', $devis) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                                {{ $devis->numero }}
                            </a>
                            <x-badge :statut="$devis->statut" class="ml-2"/>
                        </div>
                        <span class="text-sm text-gray-600 font-medium">{{ number_format($devis->montant_ttc, 2, ',', ' ') }} €</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucun devis.</p>
                @endforelse
            </div>

            {{-- Dernières factures --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Dernières factures</h3>
                    <a href="{{ route('factures.index', ['client_id' => $client->id]) }}" class="text-sm text-blue-600 hover:underline">Voir toutes</a>
                </div>
                @forelse($derniersFactures as $facture)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <a href="{{ route('factures.show', $facture) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                                {{ $facture->numero }}
                            </a>
                            <x-badge :statut="$facture->statut" class="ml-2"/>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-800">{{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €</div>
                            @if($facture->date_echeance)
                                <div class="text-xs text-gray-400">Éch. {{ $facture->date_echeance->format('d/m/Y') }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucune facture.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
