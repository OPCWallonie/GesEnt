<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Facture fournisseur</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur *</label>
            <select name="fournisseur_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                <option value="">— Sélectionner —</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" @selected(old('fournisseur_id', $facture->fournisseur_id ?? $fournisseurId) == $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
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
                        {{ $ch->nom }} ({{ $ch->client?->nom }})
                    </option>
                @endforeach
            </select>
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

@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
