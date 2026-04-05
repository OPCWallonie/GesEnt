<x-app-layout>
    <x-slot name="header">{{ $chantier->nom }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('chantiers.edit', $chantier) }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
            Modifier
        </a>
        <a href="{{ route('factures-achat.create', ['chantier_id' => $chantier->id]) }}"
           class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
            + Facture achat
        </a>
        <a href="{{ route('devis.create', ['chantier_id' => $chantier->id]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            + Nouveau devis
        </a>
    </x-slot>

    <div class="grid grid-cols-3 gap-6">

        {{-- Colonne infos --}}
        <div class="col-span-3 lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Détails</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">Statut</dt>
                        <dd class="mt-0.5"><x-badge :statut="$chantier->statut"/></dd>
                    </div>
                    @if($chantier->client)
                        <div>
                            <dt class="text-gray-400 text-xs">Client</dt>
                            <dd class="text-gray-800">
                                <a href="{{ route('clients.show', $chantier->client) }}" class="hover:text-blue-600">
                                    {{ $chantier->client->nom }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($chantier->date_debut)
                        <div>
                            <dt class="text-gray-400 text-xs">Date de début</dt>
                            <dd class="text-gray-800">{{ $chantier->date_debut->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($chantier->date_fin_prevue)
                        <div>
                            <dt class="text-gray-400 text-xs">Fin prévue</dt>
                            <dd class="text-gray-800">{{ $chantier->date_fin_prevue->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($chantier->adresse_chantier || $chantier->ville)
                        <div>
                            <dt class="text-gray-400 text-xs">Adresse</dt>
                            <dd class="text-gray-800">
                                @if($chantier->adresse_chantier){{ $chantier->adresse_chantier }}<br>@endif
                                {{ $chantier->code_postal }} {{ $chantier->ville }}<br>
                                {{ $chantier->pays }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Stats --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Documents</h3>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $devis->count() }}</div>
                        <div class="text-xs text-gray-400">Devis</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $bonsCommande->count() }}</div>
                        <div class="text-xs text-gray-400">BDC</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $factures->count() }}</div>
                        <div class="text-xs text-gray-400">Factures</div>
                    </div>
                </div>
            </div>

            {{-- Rentabilité --}}
            @php
                $ventes = $chantier->totalVentes();
                $achats = $chantier->totalAchats();
                $marge  = $chantier->marge();
                $taux   = $chantier->tauxMarge();
            @endphp
            @if($ventes > 0 || $achats > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Rentabilité</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-400">Ventes TTC</dt><dd class="font-medium text-blue-700">{{ number_format($ventes, 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Achats TTC</dt><dd class="font-medium text-orange-600">{{ number_format($achats, 2, ',', ' ') }} €</dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 mt-1">
                        <dt class="font-semibold text-gray-700">Marge brute</dt>
                        <dd class="font-bold {{ $marge >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($marge, 2, ',', ' ') }} €</dd>
                    </div>
                    @if($taux !== null)
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Taux de marge</dt>
                        <dd class="font-medium {{ $taux >= 30 ? 'text-green-600' : ($taux >= 15 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ number_format($taux, 1) }}%
                        </dd>
                    </div>
                    @endif
                </dl>
                @if($ventes > 0)
                <div class="mt-3">
                    <div class="w-full bg-orange-100 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, max(0, $taux ?? 0)) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>Achats</span><span>Marge</span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Avancement --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-700 text-sm">Avancement</h3>
                    <span class="text-sm font-bold {{ $chantier->avancement >= 100 ? 'text-green-600' : 'text-blue-600' }}">
                        {{ $chantier->avancement }}%
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all {{ $chantier->avancement >= 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                         style="width: {{ $chantier->avancement }}%"></div>
                </div>
                @if($chantier->date_debut_reel || $chantier->date_fin_reelle)
                <div class="mt-2 text-xs text-gray-400 flex gap-4">
                    @if($chantier->date_debut_reel)
                        <span>Début réel : {{ $chantier->date_debut_reel->format('d/m/Y') }}</span>
                    @endif
                    @if($chantier->date_fin_reelle)
                        <span>Fin réelle : {{ $chantier->date_fin_reelle->format('d/m/Y') }}</span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Description --}}
            @if($chantier->description)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-2 text-sm">Description</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $chantier->description }}</p>
                </div>
            @endif

            {{-- Notes --}}
            @if($chantier->notes)
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-5">
                    <h3 class="font-semibold text-yellow-800 mb-2 text-sm">Notes</h3>
                    <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ $chantier->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Colonne activité --}}
        <div class="col-span-3 lg:col-span-2 space-y-6">

            {{-- Devis --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Devis</h3>
                    <a href="{{ route('devis.create', ['chantier_id' => $chantier->id]) }}"
                       class="text-sm text-blue-600 hover:underline">+ Nouveau</a>
                </div>
                @forelse($devis as $d)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <a href="{{ route('devis.show', $d) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                                {{ $d->numero }}
                            </a>
                            <x-badge :statut="$d->statut" class="ml-2"/>
                        </div>
                        <span class="text-sm text-gray-600 font-medium">{{ number_format($d->montant_ttc, 2, ',', ' ') }} €</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucun devis pour ce chantier.</p>
                @endforelse
            </div>

            {{-- Bons de commande --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Bons de commande</h3>
                </div>
                @forelse($bonsCommande as $bdc)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <a href="{{ route('bons-commande.show', $bdc) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                                {{ $bdc->numero }}
                            </a>
                            <x-badge :statut="$bdc->statut" class="ml-2"/>
                        </div>
                        <span class="text-sm text-gray-600 font-medium">{{ number_format($bdc->montant_ttc, 2, ',', ' ') }} €</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucun bon de commande.</p>
                @endforelse
            </div>

            {{-- Factures ventes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Factures ventes</h3>
                </div>
                @forelse($factures as $facture)
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

            {{-- Factures achats --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Factures achats fournisseurs</h3>
                    <a href="{{ route('factures-achat.create', ['chantier_id' => $chantier->id]) }}"
                       class="text-sm text-blue-600 hover:underline">+ Nouvelle</a>
                </div>
                @forelse($facturesAchat as $fa)
                    <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div>
                            <a href="{{ route('factures-achat.show', $fa) }}" class="font-mono text-sm font-medium text-gray-800 hover:text-blue-600">
                                {{ $fa->numero }}
                            </a>
                            <span class="ml-2 text-xs text-gray-400">{{ $fa->fournisseur->nom }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium {{ $fa->statut === 'payee' ? 'text-green-700' : 'text-orange-600' }}">
                                {{ number_format($fa->montant_ttc, 2, ',', ' ') }} €
                            </div>
                            <div class="text-xs text-gray-400">{{ $fa->label_categorie }}</div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucune facture achat.</p>
                @endforelse
            </div>

            {{-- Journal de chantier --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Journal de chantier</h3>
                    <button type="button" @click="$dispatch('open-journal')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter une entrée
                    </button>
                </div>

                @forelse($chantier->journal as $entree)
                @php $info = $entree->type_info; @endphp
                <div class="px-5 py-4 border-b last:border-0 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                            {{ match($info['color']) {
                                'blue'   => 'bg-blue-100 text-blue-600',
                                'green'  => 'bg-green-100 text-green-600',
                                'red'    => 'bg-red-100 text-red-600',
                                'purple' => 'bg-purple-100 text-purple-600',
                                'orange' => 'bg-orange-100 text-orange-600',
                                default  => 'bg-gray-100 text-gray-500',
                            } }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">{{ $info['label'] }}</span>
                                    @if($entree->titre)
                                        <span class="text-sm font-medium text-gray-900">{{ $entree->titre }}</span>
                                    @endif
                                    @if($entree->avancement_apres !== null)
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                            → {{ $entree->avancement_apres }}%
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs text-gray-400">{{ $entree->created_at->format('d/m/Y H:i') }} — {{ $entree->user->name }}</span>
                                    @if(auth()->id() === $entree->user_id || auth()->user()->hasRole('admin'))
                                    <form method="POST" action="{{ route('chantiers.journal.destroy', $entree) }}"
                                          onsubmit="return confirm('Supprimer cette entrée ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @if($entree->contenu)
                                <p class="text-sm text-gray-600 mt-1 whitespace-pre-wrap">{{ $entree->contenu }}</p>
                            @endif
                            @if($entree->photos)
                                <div class="flex gap-2 mt-2 flex-wrap">
                                    @foreach($entree->photos as $photo)
                                        <a href="{{ Storage::url($photo) }}" target="_blank">
                                            <img src="{{ Storage::url($photo) }}" alt="Photo chantier"
                                                 class="w-20 h-20 object-cover rounded-lg border border-gray-200 hover:opacity-80">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                    <p class="px-5 py-4 text-sm text-gray-400">Aucune entrée dans le journal. Ajoutez une note, photo ou jalon !</p>
                @endforelse
            </div>

        </div>
    </div>

    {{-- Modal ajout journal --}}
    <div x-data="{ open: false }" @open-journal.window="open = true"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Ajouter au journal</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('chantiers.journal.store', $chantier) }}" method="POST"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            @foreach(\App\Models\JournalChantier::TYPES as $key => $t)
                                <option value="{{ $key }}">{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Avancement après (%)</label>
                        <input type="number" name="avancement_apres" min="0" max="100"
                               value="{{ $chantier->avancement }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre (optionnel)</label>
                    <input type="text" name="titre" maxlength="255"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                    <textarea name="contenu" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                              placeholder="Décrivez l'avancement, le problème rencontré, la décision prise…"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photos (optionnel)</label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">Plusieurs photos acceptées (max 5 Mo chacune)</p>
                </div>
                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
