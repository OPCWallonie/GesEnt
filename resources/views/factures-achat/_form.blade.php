<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Facture fournisseur</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-combobox
                name="fournisseur_id"
                label="Fournisseur"
                :endpoint="route('fournisseurs.api-search')"
                :value="old('fournisseur_id', isset($facture) && $facture ? $facture->fournisseur_id : ($fournisseurId ?? null))"
                :text="isset($facture) && $facture ? ($facture->fournisseur?->nom ?? '') : ($fournisseurSelectionne?->nom ?? '')"
                :required="true"
                placeholder="Rechercher un fournisseur…"
                :allow-create="true"
                create-label="Nouveau fournisseur"
                :create-url="route('fournisseurs.quick-create')"
                :create-fields="[
                    ['name' => 'nom', 'label' => 'Nom', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'text'],
                ]"
            />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Référence fournisseur</label>
            <input type="text" name="reference_fournisseur" value="{{ old('reference_fournisseur', $facture->reference_fournisseur ?? '') }}"
                   placeholder="N° de facture du fournisseur"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
            <select name="categorie" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                @foreach(\App\Models\FactureAchat::$categories as $val => $label)
                    <option value="{{ $val }}" @selected(old('categorie', $facture->categorie ?? 'materiel') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Chantier lié</label>
            <select name="chantier_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="">— Aucun —</option>
                @foreach($chantiers as $ch)
                    <option value="{{ $ch->id }}" @selected(old('chantier_id', $facture->chantier_id ?? $chantierId) == $ch->id)>
                        {{ $ch->reference ? '[' . $ch->reference . '] ' : '' }}{{ $ch->nom }}{{ $ch->client ? ' (' . $ch->client->nom . ')' : '' }}
                    </option>
                @endforeach
            </select>
            {{-- Bandeau de confiance OCR (affiché uniquement en création) --}}
            @if(!isset($facture) || !$facture)
            <div x-show="chantierMessage" x-cloak class="mt-1.5 text-xs rounded-lg px-3 py-2 flex items-start gap-1.5"
                 :class="{
                     'bg-green-50 text-green-700 border border-green-200': chantierConfiance === 'haute',
                     'bg-amber-50 text-amber-700 border border-amber-200': chantierConfiance === 'moyenne',
                     'bg-blue-50 text-blue-700 border border-blue-200':   chantierConfiance === 'basse',
                 }">
                <span x-show="chantierConfiance === 'haute'">✓</span>
                <span x-show="chantierConfiance === 'moyenne'">~</span>
                <span x-show="chantierConfiance === 'basse'">💡</span>
                <span x-text="chantierMessage"></span>
            </div>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bon de commande lié</label>
            <select name="bon_commande_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="">— Aucun —</option>
                @foreach($bonsCommande as $bdc)
                    <option value="{{ $bdc->id }}" @selected(old('bon_commande_id', $facture->bon_commande_id ?? '') == $bdc->id)>
                        {{ $bdc->numero }} — {{ $bdc->client?->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select name="statut" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="en_attente" @selected(old('statut', $facture->statut ?? 'en_attente') === 'en_attente')>En attente</option>
                <option value="payee" @selected(old('statut', $facture->statut ?? '') === 'payee')>Payée</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date de facture *</label>
            <input type="date" name="date_document" value="{{ old('date_document', isset($facture) ? $facture->date_document->format('Y-m-d') : date('Y-m-d')) }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance</label>
            <input type="date" name="date_echeance" value="{{ old('date_echeance', isset($facture) ? $facture->date_echeance?->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement</label>
            <input type="date" name="date_paiement" value="{{ old('date_paiement', isset($facture) ? $facture->date_paiement?->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Montants</h2>
    <div class="grid grid-cols-3 gap-4" x-data="{
        ht: {{ old('montant_ht', $facture->montant_ht ?? 0) }},
        tva: {{ old('taux_tva', $facture->taux_tva ?? 21) }},
        get ttc() { return (parseFloat(this.ht) * (1 + parseFloat(this.tva)/100)).toFixed(2); },
        get montantTva() { return (parseFloat(this.ht) * parseFloat(this.tva)/100).toFixed(2); }
    }">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Montant HT (€) *</label>
            <input type="number" name="montant_ht" x-model="ht" step="0.01" min="0" required
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Taux TVA (%)</label>
            <input type="number" name="taux_tva" x-model="tva" step="0.01" min="0" max="100"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Montant TTC (€)</label>
            <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-800" x-text="ttc + ' €'"></div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
    <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes', $facture->notes ?? '') }}</textarea>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Document original</h2>
    @if(isset($facture) && $facture && $facture->has_fichier)
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-700 truncate">{{ $facture->fichier_nom_original }}</div>
                <div class="text-xs text-gray-400">{{ $facture->fichier_mime }}</div>
            </div>
            <a href="{{ $facture->fichier_url }}" target="_blank"
               class="text-xs text-blue-600 hover:underline flex-shrink-0">Voir</a>
        </div>
        <label class="block text-xs text-gray-500">Remplacer le document</label>
    @endif
    <input type="file" name="fichier_original" id="fichier_original_input"
           accept=".pdf,.jpg,.jpeg,.png"
           class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
    <p class="text-xs text-gray-400">PDF ou image (jpg, png). Facultatif.</p>
</div>

@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
