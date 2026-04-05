<x-app-layout>
    <x-slot name="header">Modifier — {{ $client->nom }}</x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Identité</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom', $client->nom) }}" required
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut juridique</label>
                        <select name="statut_juridique" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">—</option>
                            @foreach(['SA','SARL','SRL','SPRL','SNC','SCRL','ASBL','Indépendant'] as $s)
                                <option value="{{ $s }}" @selected(old('statut_juridique', $client->statut_juridique) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $client->adresse) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                        <input type="text" name="code_postal" value="{{ old('code_postal', $client->code_postal) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville', $client->ville) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                        <input type="text" name="pays" value="{{ old('pays', $client->pays) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Contact</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input type="text" name="telephone" value="{{ old('telephone', $client->telephone) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GSM</label>
                        <input type="text" name="gsm" value="{{ old('gsm', $client->gsm) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $client->email) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                        <input type="url" name="site_web" value="{{ old('site_web', $client->site_web) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
                <h2 class="font-semibold text-gray-700 border-b pb-2">Identification fiscale</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">N° TVA</label>
                        <input type="text" name="numero_tva" value="{{ old('numero_tva', $client->numero_tva) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono @error('numero_tva') border-red-400 @enderror">
                        @error('numero_tva')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code client</label>
                        <input type="text" name="code_client" value="{{ old('code_client', $client->code_client) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm @error('code_client') border-red-400 @enderror">
                        @error('code_client')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes internes</label>
                    <textarea name="notes" rows="3"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                    ← Retour
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
