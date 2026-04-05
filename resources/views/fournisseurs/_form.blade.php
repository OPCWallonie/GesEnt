<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Identification</h2>
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
            <input type="text" name="nom" value="{{ old('nom', $fournisseur->nom ?? '') }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Personne de contact</label>
            <input type="text" name="contact" value="{{ old('contact', $fournisseur->contact ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $fournisseur->email ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <input type="text" name="telephone" value="{{ old('telephone', $fournisseur->telephone ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">N° TVA</label>
            <input type="text" name="numero_tva" value="{{ old('numero_tva', $fournisseur->numero_tva ?? '') }}"
                   placeholder="BE 0000.000.000"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">N° d'entreprise</label>
            <input type="text" name="numero_entreprise" value="{{ old('numero_entreprise', $fournisseur->numero_entreprise ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <h2 class="font-semibold text-gray-700 border-b pb-2">Adresse</h2>
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
            <input type="text" name="adresse" value="{{ old('adresse', $fournisseur->adresse ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
            <input type="text" name="code_postal" value="{{ old('code_postal', $fournisseur->code_postal ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
            <input type="text" name="ville" value="{{ old('ville', $fournisseur->ville ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
            <input type="text" name="pays" value="{{ old('pays', $fournisseur->pays ?? 'Belgique') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
        </div>
        <div class="flex items-center gap-3 pt-2">
            <input type="checkbox" name="actif" id="actif" value="1"
                   {{ old('actif', ($fournisseur->actif ?? true) ? '1' : '0') == '1' ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600">
            <label for="actif" class="text-sm font-medium text-gray-700">Fournisseur actif</label>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
    <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes', $fournisseur->notes ?? '') }}</textarea>
</div>

@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
