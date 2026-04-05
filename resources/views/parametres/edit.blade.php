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

        {{-- Facturation électronique Peppol --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4"
             id="peppol"
             x-data="{ mode: '{{ old('peppol_mode', $parametres->peppol_mode ?? 'desactive') }}', provider: '{{ old('peppol_provider', $parametres->peppol_provider) }}' }">

            <h2 class="font-semibold text-gray-700 border-b pb-2">Facturation électronique (Peppol)</h2>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
                <p class="font-semibold">Obligation légale belge depuis le 1<sup>er</sup> janvier 2026</p>
                <p class="mt-1">
                    Toutes les factures B2B doivent être envoyées via le réseau Peppol.
                    Les factures PDF par email ne sont plus suffisantes.
                    Choisissez ci-dessous comment Gesent gère cette obligation.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mode de fonctionnement</label>
                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer"
                           :class="mode === 'desactive' ? 'border-amber-300 bg-amber-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" name="peppol_mode" value="desactive" x-model="mode"
                               class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="font-medium text-gray-900">Peppol désactivé</span>
                            <span class="ml-2 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Nécessite un logiciel comptable</span>
                            <p class="text-xs text-gray-500 mt-1">
                                Gesent génère les factures en PDF. Vous devez exporter vers votre logiciel
                                comptable (Winbooks, BOB, Exact…) qui se charge de l'envoi Peppol.
                            </p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer"
                           :class="mode === 'envoi' ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" name="peppol_mode" value="envoi" x-model="mode"
                               class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="font-medium text-gray-900">Peppol envoi uniquement</span>
                            <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Recommandé</span>
                            <p class="text-xs text-gray-500 mt-1">
                                Gesent envoie les factures de vente directement via Peppol + une copie PDF
                                de courtoisie par email. La réception des factures achats reste gérée par
                                votre comptable.
                            </p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer"
                           :class="mode === 'complet' ? 'border-green-300 bg-green-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" name="peppol_mode" value="complet" x-model="mode"
                               class="mt-0.5 text-green-600 focus:ring-green-500">
                        <div>
                            <span class="font-medium text-gray-900">Peppol complet (autonome)</span>
                            <span class="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Lot 6B — bientôt disponible</span>
                            <p class="text-xs text-gray-500 mt-1">
                                Gesent envoie ET reçoit via Peppol. Les factures fournisseurs arrivent
                                automatiquement. Pas besoin de logiciel comptable pour l'envoi/réception.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Configuration Access Point (visible si mode != desactive) --}}
            <div x-show="mode !== 'desactive'" x-cloak class="space-y-4 pt-2 border-t border-gray-100">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Access Point Peppol</label>
                    <select name="peppol_provider" x-model="provider"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">— Choisir un provider —</option>
                        <option value="storecove" @selected(old('peppol_provider', $parametres->peppol_provider) === 'storecove')>
                            Storecove — API REST/JSON, international, sandbox gratuite
                        </option>
                        <option value="billit" @selected(old('peppol_provider', $parametres->peppol_provider) === 'billit')>
                            Billit — Belge, populaire, 15 jours d'essai gratuit
                        </option>
                        <option value="einvoice_be" @selected(old('peppol_provider', $parametres->peppol_provider) === 'einvoice_be')>
                            e-invoice.be — Belge, pay-per-use (0,25 €/facture), SDK PHP
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clé API</label>
                        <input type="password" name="peppol_api_key"
                               placeholder="{{ $parametres->peppol_api_key ? '••••••••• (laisser vide pour conserver)' : 'Votre clé API' }}"
                               autocomplete="new-password"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ID entité légale
                            <span class="text-xs text-gray-400 font-normal">— fourni par le provider</span>
                        </label>
                        <input type="text" name="peppol_entity_id"
                               value="{{ old('peppol_entity_id', $parametres->peppol_entity_id) }}"
                               placeholder="Ex: 12345"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Peppol ID entreprise
                            <span class="text-xs text-gray-400 font-normal">— format 0208:BEXXXXXXXXXX</span>
                        </label>
                        <input type="text" name="peppol_id"
                               value="{{ old('peppol_id', $parametres->peppol_id) }}"
                               placeholder="0208:BE0123456789"
                               class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                        <p class="text-xs text-gray-400 mt-1">0208: = schéma BCE belge, suivi de votre n° d'entreprise.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Environnement</label>
                        <select name="peppol_environment" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="sandbox" @selected(($parametres->peppol_environment ?? 'sandbox') === 'sandbox')>
                                Sandbox (test)
                            </option>
                            <option value="production" @selected(($parametres->peppol_environment ?? '') === 'production')>
                                Production
                            </option>
                        </select>
                        <p class="text-xs text-amber-600 mt-1">Commencez en sandbox pour tester, puis passez en production.</p>
                    </div>
                </div>

                {{-- Infos providers --}}
                <div class="grid grid-cols-1 gap-2">
                    <div x-show="provider === 'storecove'" class="text-xs bg-indigo-50 border border-indigo-200 rounded p-3 text-indigo-700">
                        <strong>Storecove</strong> — API REST/JSON, sandbox gratuite.
                        Créez un compte sur <em>app.storecove.com</em>, créez une Legal Entity, copiez le <code>legal_entity_id</code>.
                        <a href="https://www.storecove.com/docs/" target="_blank" class="underline">Documentation API →</a>
                    </div>
                    <div x-show="provider === 'billit'" class="text-xs bg-sky-50 border border-sky-200 rounded p-3 text-sky-700">
                        <strong>Billit</strong> — Solution belge, 15 jours d'essai.
                        Créez un compte sur <em>app.billit.eu</em>, récupérez votre Party ID dans les paramètres.
                    </div>
                    <div x-show="provider === 'einvoice_be'" class="text-xs bg-teal-50 border border-teal-200 rounded p-3 text-teal-700">
                        <strong>e-invoice.be</strong> — Solution belge pay-per-use (0,25 €/facture).
                        Créez un compte sur <em>e-invoice.be</em>, récupérez votre clé API dans l'espace client.
                    </div>
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
