<x-app-layout>
    <x-slot name="header">Paramètres de l'entreprise</x-slot>

    <form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        {{-- Identité --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Identité</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise *</label>
                    <input type="text" name="nom" value="{{ old('nom', $parametres->nom) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forme juridique</label>
                    <input type="text" name="statut_juridique" value="{{ old('statut_juridique', $parametres->statut_juridique) }}"
                           placeholder="SPRL, SA, Indépendant…"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $parametres->adresse) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                    <input type="text" name="code_postal" value="{{ old('code_postal', $parametres->code_postal) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $parametres->ville) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                    <input type="text" name="pays" value="{{ old('pays', $parametres->pays) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $parametres->telephone) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $parametres->email) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                    <input type="url" name="site_web" value="{{ old('site_web', $parametres->site_web) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° TVA</label>
                    <input type="text" name="numero_tva" value="{{ old('numero_tva', $parametres->numero_tva) }}"
                           placeholder="BE 0000.000.000"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° d'entreprise</label>
                    <input type="text" name="numero_entreprise" value="{{ old('numero_entreprise', $parametres->numero_entreprise) }}"
                           placeholder="0000.000.000"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>

            {{-- Logo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                @if($parametres->logo_path)
                    <div class="mb-2">
                        <img src="{{ Storage::url($parametres->logo_path) }}" alt="Logo" class="h-16 object-contain border rounded p-1">
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                <p class="text-xs text-gray-400 mt-1">PNG ou JPG, max 2 Mo. Recommandé : fond transparent.</p>
            </div>
        </div>

        {{-- Coordonnées bancaires --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Coordonnées bancaires</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
                    <input type="text" name="iban" value="{{ old('iban', $parametres->iban) }}"
                           placeholder="BE00 0000 0000 0000"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BIC/SWIFT</label>
                    <input type="text" name="bic" value="{{ old('bic', $parametres->bic) }}"
                           placeholder="GEBABEBB"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                </div>
                <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la banque</label>
                    <input type="text" name="banque" value="{{ old('banque', $parametres->banque) }}"
                           placeholder="BNP Paribas Fortis"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
        </div>

        {{-- Valeurs par défaut --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Valeurs par défaut</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délai de règlement (jours)</label>
                    <input type="number" name="delai_reglement_defaut" value="{{ old('delai_reglement_defaut', $parametres->delai_reglement_defaut) }}" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Validité devis (jours)</label>
                    <input type="number" name="validite_devis_defaut" value="{{ old('validite_devis_defaut', $parametres->validite_devis_defaut) }}" min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>
            </div>
        </div>

        {{-- Textes légaux --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Mentions légales</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conditions générales de vente</label>
                <textarea name="conditions_generales" rows="6"
                          class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono text-xs">{{ old('conditions_generales', $parametres->conditions_generales) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Affiché au bas des devis et factures PDF.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mentions pied de page</label>
                <textarea name="mentions_pied_page" rows="3"
                          class="w-full rounded-lg border-gray-300 shadow-sm text-sm">{{ old('mentions_pied_page', $parametres->mentions_pied_page) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Pied de page sur tous les documents PDF.</p>
            </div>
        </div>

        {{-- Intelligence artificielle --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4" x-data="{ provider: '{{ old('ai_provider', $parametres->ai_provider) }}' }">
            <h2 class="font-semibold text-gray-700 border-b pb-2">Intelligence artificielle (OCR factures)</h2>
            <p class="text-sm text-gray-500">Permettez à l'application d'extraire automatiquement les données d'une facture PDF ou photo.</p>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Moteur IA</label>
                    <select name="ai_provider" x-model="provider" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">— Désactivé —</option>
                        @foreach(\App\Services\OcrFactureService::providers() as $slug => $info)
                            <option value="{{ $slug }}" @selected(old('ai_provider', $parametres->ai_provider) === $slug)>
                                {{ $info['nom'] }} — {{ $info['prix'] }}{{ $info['gratuit'] ? ' ✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Clé API — masquée pour Ollama --}}
                <div x-show="provider && provider !== 'ollama'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Clé API</label>
                    <input type="password" name="ai_api_key"
                           placeholder="{{ $parametres->ai_api_key ? '••••••••••••••• (laisser vide pour conserver)' : 'sk-...' }}"
                           autocomplete="new-password"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                    @if($parametres->ai_api_key)
                        <p class="text-xs text-green-600 mt-1">Clé enregistrée. Laissez vide pour ne pas la modifier.</p>
                    @endif
                </div>

                {{-- Modèle optionnel --}}
                <div x-show="provider" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modèle <span class="text-gray-400">(optionnel)</span></label>
                    <input type="text" name="ai_model" value="{{ old('ai_model', $parametres->ai_model) }}"
                           placeholder="Laisser vide pour le modèle par défaut"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                    <p class="text-xs text-gray-400 mt-1">
                        Défauts : claude-haiku-4-5 · gpt-4o-mini · gemini-1.5-flash · pixtral-12b · llava
                    </p>
                </div>

                {{-- URL Ollama --}}
                <div x-show="provider === 'ollama'" x-cloak class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL Ollama</label>
                    <input type="url" name="ai_url" value="{{ old('ai_url', $parametres->ai_url) }}"
                           placeholder="http://localhost:11434"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                </div>
            </div>

            {{-- Info providers --}}
            <div x-show="provider" x-cloak class="grid grid-cols-1 gap-2 mt-2">
                <div x-show="provider === 'gemini'" class="text-xs bg-green-50 border border-green-200 rounded p-3 text-green-700">
                    <strong>Gemini (Google)</strong> — Tier gratuit disponible. Créez une clé sur <em>aistudio.google.com</em>. Limite : ~1 500 requêtes/jour gratuitement.
                </div>
                <div x-show="provider === 'claude'" class="text-xs bg-purple-50 border border-purple-200 rounded p-3 text-purple-700">
                    <strong>Claude Haiku</strong> — Très économique (~$0.001/page). Clé API sur <em>console.anthropic.com</em>. Supporte aussi les PDFs scannés via vision.
                </div>
                <div x-show="provider === 'openai'" class="text-xs bg-blue-50 border border-blue-200 rounded p-3 text-blue-700">
                    <strong>GPT-4o-mini</strong> — Bon rapport qualité/prix (~$0.002/page). Clé API sur <em>platform.openai.com</em>.
                </div>
                <div x-show="provider === 'mistral'" class="text-xs bg-orange-50 border border-orange-200 rounded p-3 text-orange-700">
                    <strong>Mistral AI</strong> — Le moins cher parmi les clouds (~$0.0005/page). Clé sur <em>console.mistral.ai</em>.
                </div>
                <div x-show="provider === 'ollama'" class="text-xs bg-gray-50 border border-gray-200 rounded p-3 text-gray-700">
                    <strong>Ollama (local)</strong> — Totalement gratuit, fonctionne hors-ligne. Installez Ollama sur votre serveur et téléchargez <code>llava</code> ou <code>llama3.2-vision</code>. Aucune donnée envoyée à l'extérieur.
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Sauvegarder
            </button>
        </div>
    </form>
</x-app-layout>
