<x-app-layout>
    <x-slot name="header">Paramètres de l'entreprise</x-slot>

    <form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div x-data="{ onglet: localStorage.getItem('parametres_onglet') || 'entreprise' }"
             x-init="$watch('onglet', v => localStorage.setItem('parametres_onglet', v))">

            {{-- Navigation par onglets --}}
            <div class="flex border-b border-gray-200 mb-6 gap-0 overflow-x-auto">
                @php
                    $tabs = [
                        'entreprise'   => 'Entreprise',
                        'facturation'  => 'Facturation',
                        'integrations' => 'Intégrations',
                        'email'        => 'Email',
                        'securite'     => 'Sécurité',
                        'rh'           => 'RH & Formation',
                        'catalogue'    => 'Catalogue',
                    ];
                @endphp
                @foreach($tabs as $id => $label)
                <button type="button" @click="onglet = '{{ $id }}'"
                        :class="onglet === '{{ $id }}'
                            ? 'border-b-2 border-blue-600 text-blue-600'
                            : 'text-gray-500 hover:text-gray-700 hover:border-b-2 hover:border-gray-200'"
                        class="px-5 py-3 text-sm font-medium whitespace-nowrap -mb-px transition-colors">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- ════════════════════════════════════
                 Onglet ENTREPRISE
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'entreprise'" class="space-y-6">

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

            </div>

            {{-- ════════════════════════════════════
                 Onglet FACTURATION
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'facturation'" x-cloak class="space-y-6">

                {{-- Valeurs par défaut --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Valeurs par défaut</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Délai de règlement (jours)</label>
                            <input type="number" name="delai_reglement_defaut"
                                   value="{{ old('delai_reglement_defaut', $parametres->delai_reglement_defaut) }}" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Validité devis (jours)</label>
                            <input type="number" name="validite_devis_defaut"
                                   value="{{ old('validite_devis_defaut', $parametres->validite_devis_defaut) }}" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                {{-- Mentions légales --}}
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

            </div>

            {{-- ════════════════════════════════════
                 Onglet INTÉGRATIONS
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'integrations'" x-cloak class="space-y-6">

                {{-- Intelligence artificielle (OCR) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4"
                     x-data="{ provider: '{{ old('ai_provider', $parametres->ai_provider) }}' }">
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

                        <div x-show="provider" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modèle <span class="text-gray-400">(optionnel)</span></label>
                            <input type="text" name="ai_model" value="{{ old('ai_model', $parametres->ai_model) }}"
                                   placeholder="Laisser vide pour le modèle par défaut"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                            <p class="text-xs text-gray-400 mt-1">
                                Défauts : claude-haiku-4-5 · gpt-4o-mini · gemini-1.5-flash · pixtral-12b · llava
                            </p>
                        </div>

                        <div x-show="provider === 'ollama'" x-cloak class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Ollama</label>
                            <input type="url" name="ai_url" value="{{ old('ai_url', $parametres->ai_url) }}"
                                   placeholder="http://localhost:11434"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                        </div>
                    </div>

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
                            <strong>Ollama (local)</strong> — Totalement gratuit, fonctionne hors-ligne. Installez Ollama sur votre serveur et téléchargez <code>llava</code> ou <code>llama3.2-vision</code>.
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
                                    <p class="text-xs text-gray-500 mt-1">Gesent génère les factures en PDF. Vous devez exporter vers votre logiciel comptable (Winbooks, BOB, Exact…) qui se charge de l'envoi Peppol.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer"
                                   :class="mode === 'envoi' ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="peppol_mode" value="envoi" x-model="mode"
                                       class="mt-0.5 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="font-medium text-gray-900">Peppol envoi uniquement</span>
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Recommandé</span>
                                    <p class="text-xs text-gray-500 mt-1">Gesent envoie les factures de vente directement via Peppol + une copie PDF de courtoisie par email.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer"
                                   :class="mode === 'complet' ? 'border-green-300 bg-green-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="peppol_mode" value="complet" x-model="mode"
                                       class="mt-0.5 text-green-600 focus:ring-green-500">
                                <div>
                                    <span class="font-medium text-gray-900">Peppol complet (autonome)</span>
                                    <p class="text-xs text-gray-500 mt-1">Gesent envoie ET reçoit via Peppol. Les factures fournisseurs arrivent automatiquement.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div x-show="mode !== 'desactive'" x-cloak class="space-y-4 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Access Point Peppol</label>
                            <select name="peppol_provider" x-model="provider" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="">— Choisir un provider —</option>
                                <option value="storecove" @selected(old('peppol_provider', $parametres->peppol_provider) === 'storecove')>Storecove — API REST/JSON, international, sandbox gratuite</option>
                                <option value="billit" @selected(old('peppol_provider', $parametres->peppol_provider) === 'billit')>Billit — Belge, populaire, 15 jours d'essai gratuit</option>
                                <option value="einvoice_be" @selected(old('peppol_provider', $parametres->peppol_provider) === 'einvoice_be')>e-invoice.be — Belge, pay-per-use (0,25 €/facture), SDK PHP</option>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID entité légale <span class="text-xs text-gray-400 font-normal">— fourni par le provider</span></label>
                                <input type="text" name="peppol_entity_id"
                                       value="{{ old('peppol_entity_id', $parametres->peppol_entity_id) }}"
                                       placeholder="Ex: 12345"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Peppol ID <span class="text-xs text-gray-400 font-normal">— format 0208:BEXXXXXXXXXX</span></label>
                                <input type="text" name="peppol_id"
                                       value="{{ old('peppol_id', $parametres->peppol_id) }}"
                                       placeholder="0208:BE0123456789"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm font-mono">
                                <p class="text-xs text-gray-400 mt-1">0208: = schéma BCE belge, suivi de votre n° d'entreprise.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Environnement</label>
                                <select name="peppol_environment" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    <option value="sandbox" @selected(($parametres->peppol_environment ?? 'sandbox') === 'sandbox')>Sandbox (test)</option>
                                    <option value="production" @selected(($parametres->peppol_environment ?? '') === 'production')>Production</option>
                                </select>
                                <p class="text-xs text-amber-600 mt-1">Commencez en sandbox pour tester, puis passez en production.</p>
                            </div>
                        </div>

                        <div x-show="mode === 'complet'" x-cloak class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-3">
                            <h3 class="text-sm font-semibold text-green-800">Configuration réception Peppol</h3>
                            <p class="text-xs text-green-700">Configurez cette URL dans votre Access Point comme webhook de réception.</p>
                            @if($parametres->peppol_webhook_token)
                            <div class="flex items-center gap-2">
                                <code class="flex-1 text-xs bg-white border border-green-300 rounded px-3 py-2 font-mono select-all break-all">
                                    {{ route('webhook.peppol') }}?token={{ $parametres->peppol_webhook_token }}
                                </code>
                                <button type="button"
                                        onclick="navigator.clipboard.writeText('{{ route('webhook.peppol') }}?token={{ $parametres->peppol_webhook_token }}').then(() => this.textContent = '✓').catch(() => {}); setTimeout(() => this.textContent = 'Copier', 2000)"
                                        class="px-3 py-2 bg-green-600 text-white text-xs rounded hover:bg-green-700 flex-shrink-0">
                                    Copier
                                </button>
                            </div>
                            @else
                            <p class="text-xs text-green-600 italic">Sauvegardez en mode "complet" pour générer l'URL webhook.</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <div x-show="provider === 'storecove'" class="text-xs bg-indigo-50 border border-indigo-200 rounded p-3 text-indigo-700">
                                <strong>Storecove</strong> — API REST/JSON, sandbox gratuite. Créez un compte sur <em>app.storecove.com</em>, créez une Legal Entity, copiez le <code>legal_entity_id</code>.
                            </div>
                            <div x-show="provider === 'billit'" class="text-xs bg-sky-50 border border-sky-200 rounded p-3 text-sky-700">
                                <strong>Billit</strong> — Solution belge, 15 jours d'essai. Créez un compte sur <em>app.billit.eu</em>, récupérez votre Party ID dans les paramètres.
                            </div>
                            <div x-show="provider === 'einvoice_be'" class="text-xs bg-teal-50 border border-teal-200 rounded p-3 text-teal-700">
                                <strong>e-invoice.be</strong> — Solution belge pay-per-use (0,25 €/facture). Créez un compte sur <em>e-invoice.be</em>.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Intégration Odoo --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4"
                     id="odoo"
                     x-data="{
                         actif: {{ $parametres->odoo_actif ? 'true' : 'false' }},
                         test: null,
                         loading: false,
                         async testerConnexion() {
                             this.loading = true;
                             this.test = null;
                             try {
                                 const r = await fetch('{{ route('odoo.test') }}', {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                         'Accept': 'application/json',
                                     },
                                     body: JSON.stringify({
                                         url:      document.querySelector('[name=odoo_url]')?.value,
                                         database: document.querySelector('[name=odoo_database]')?.value,
                                         username: document.querySelector('[name=odoo_username]')?.value,
                                         api_key:  document.querySelector('[name=odoo_api_key]')?.value,
                                     }),
                                 });
                                 this.test = await r.json();
                             } catch (e) {
                                 this.test = { success: false, message: 'Impossible de contacter le serveur.' };
                             } finally {
                                 this.loading = false;
                             }
                         }
                     }">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h2 class="font-semibold text-gray-700">Intégration Odoo</h2>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="odoo_actif" value="1" x-model="actif"
                                   {{ $parametres->odoo_actif ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-600">Activer la synchronisation Odoo</span>
                        </label>
                    </div>
                    <div class="text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
                        Synchronisation bidirectionnelle avec votre instance Odoo. Option facultative — sans impact sur le fonctionnement normal si désactivé.
                    </div>
                    <div x-show="actif" x-cloak class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL Odoo</label>
                                <input type="url" name="odoo_url" value="{{ old('odoo_url', $parametres->odoo_url) }}"
                                       placeholder="https://monentreprise.odoo.com"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Base de données</label>
                                <input type="text" name="odoo_database" value="{{ old('odoo_database', $parametres->odoo_database) }}"
                                       placeholder="nom_de_la_base"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Login (email)</label>
                                <input type="text" name="odoo_username" value="{{ old('odoo_username', $parametres->odoo_username) }}"
                                       placeholder="admin@monentreprise.be"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Clé API Odoo</label>
                                <input type="password" name="odoo_api_key"
                                       placeholder="{{ $parametres->odoo_api_key ? '(conservée — laisser vide pour ne pas changer)' : 'Clé API Odoo…' }}"
                                       autocomplete="new-password"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="testerConnexion()" :disabled="loading"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 disabled:opacity-50">
                                <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <span x-show="!loading">Tester la connexion</span>
                                <span x-show="loading">Test en cours…</span>
                            </button>
                            <div x-show="test !== null" class="text-sm">
                                <span x-show="test?.success" class="text-green-600 font-medium" x-text="test?.message"></span>
                                <span x-show="!test?.success" class="text-red-600" x-text="test?.message"></span>
                            </div>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                            <h3 class="text-sm font-semibold text-gray-700">Mapping comptable</h3>
                            <p class="text-xs text-gray-500">Codes journaux et comptes de votre plan comptable Odoo.</p>
                            <div class="grid grid-cols-2 gap-3">
                                @php
                                    $mapping = $parametres->odoo_mapping ?? [];
                                    $mappingFields = [
                                        'journal_vente'      => ['Journal ventes', 'ex: SAJ'],
                                        'journal_achat'      => ['Journal achats', 'ex: ACH'],
                                        'compte_client'      => ['Compte clients', 'ex: 400000'],
                                        'compte_fournisseur' => ['Compte fournisseurs', 'ex: 440000'],
                                        'compte_vente'       => ['Compte produits ventes', 'ex: 700000'],
                                        'compte_achat'       => ['Compte charges achats', 'ex: 600000'],
                                        'compte_tva_21'      => ['Compte TVA 21%', 'ex: 451000'],
                                        'compte_tva_6'       => ['Compte TVA 6%', 'ex: 451060'],
                                    ];
                                @endphp
                                @foreach($mappingFields as $key => [$label, $placeholder])
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                                    <input type="text" name="odoo_mapping[{{ $key }}]"
                                           value="{{ old("odoo_mapping.$key", $mapping[$key] ?? '') }}"
                                           placeholder="{{ $placeholder }}"
                                           class="w-full rounded border-gray-300 text-xs shadow-sm">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        @if($parametres->peppolActif())
                        <div class="border border-amber-200 bg-amber-50 rounded-lg p-4 space-y-2">
                            <h3 class="text-sm font-semibold text-amber-800">Peppol — qui envoie les factures ?</h3>
                            <p class="text-xs text-amber-700">Vous avez Peppol activé ET Odoo connecté. Pour éviter les doublons, choisissez qui gère l'envoi.</p>
                            <div class="flex gap-6 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="peppol_gere_par" value="gesent"
                                           {{ old('peppol_gere_par', $parametres->peppol_gere_par ?? 'gesent') === 'gesent' ? 'checked' : '' }}
                                           class="text-blue-600 border-gray-300">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Gesent envoie via Peppol</span>
                                        <p class="text-xs text-gray-500">Gesent transmet les factures avant de les envoyer à Odoo.</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="peppol_gere_par" value="odoo"
                                           {{ old('peppol_gere_par', $parametres->peppol_gere_par ?? 'gesent') === 'odoo' ? 'checked' : '' }}
                                           class="text-blue-600 border-gray-300">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">Odoo gère Peppol</span>
                                        <p class="text-xs text-gray-500">Gesent désactive son envoi Peppol — c'est Odoo qui s'en charge.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @else
                            <input type="hidden" name="peppol_gere_par" value="gesent">
                        @endif
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════
                 Onglet EMAIL
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'email'" x-cloak class="space-y-6">

                {{-- Configuration SMTP --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Configuration SMTP</h2>

                    {{-- Presets --}}
                    <div x-data="smtpPresets()" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preset fournisseur</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="preset in presets" :key="preset.label">
                                <button type="button" @click="appliquer(preset)"
                                        class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                                    <span x-text="preset.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hôte SMTP</label>
                            <input type="text" name="mail_host" id="mail_host"
                                   value="{{ old('mail_host', $parametres->mail_host) }}"
                                   placeholder="smtp.gmail.com"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                            <input type="number" name="mail_port" id="mail_port"
                                   value="{{ old('mail_port', $parametres->mail_port) }}"
                                   placeholder="587"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chiffrement</label>
                            <select name="mail_encryption" id="mail_encryption"
                                    class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <option value="" {{ !$parametres->mail_encryption ? 'selected' : '' }}>Aucun</option>
                                <option value="tls" {{ old('mail_encryption', $parametres->mail_encryption) === 'tls' ? 'selected' : '' }}>TLS (STARTTLS)</option>
                                <option value="ssl" {{ old('mail_encryption', $parametres->mail_encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom expéditeur</label>
                            <input type="text" name="mail_from_name"
                                   value="{{ old('mail_from_name', $parametres->mail_from_name) }}"
                                   placeholder="{{ $parametres->nom }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur SMTP</label>
                            <input type="text" name="mail_username"
                                   value="{{ old('mail_username', $parametres->mail_username) }}"
                                   placeholder="votre@email.com"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe SMTP</label>
                            <input type="password" name="mail_password" autocomplete="new-password"
                                   placeholder="{{ $parametres->mail_password ? '••••••••' : 'Nouveau mot de passe' }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            @if($parametres->mail_password)
                                <p class="text-xs text-gray-400 mt-1">Laisser vide pour conserver le mot de passe actuel.</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse expéditeur (From)</label>
                            <input type="email" name="mail_from_address"
                                   value="{{ old('mail_from_address', $parametres->mail_from_address) }}"
                                   placeholder="{{ $parametres->email }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    {{-- Test email --}}
                    <div x-data="{ testEmail: '', testResult: null, loading: false }" class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Tester la configuration</h3>
                        <div class="flex gap-3">
                            <input type="email" x-model="testEmail" placeholder="email@destinataire.com"
                                   class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
                            <button type="button" :disabled="loading || !testEmail"
                                    @click="loading=true; testResult=null;
                                        fetch('{{ route('parametres.tester-email') }}', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                            body: JSON.stringify({ email: testEmail })
                                        })
                                        .then(r => r.json())
                                        .then(d => { testResult = d; loading = false; })
                                        .catch(() => { testResult = { success: false, message: 'Erreur réseau' }; loading = false; })"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <span x-text="loading ? 'Envoi…' : 'Envoyer un test'"></span>
                            </button>
                        </div>
                        <div x-show="testResult !== null" class="mt-2 text-sm rounded-lg px-3 py-2"
                             :class="testResult?.success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                            <span x-text="testResult?.message"></span>
                        </div>
                    </div>
                </div>

                {{-- Signature --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Signature email</h2>
                    <p class="text-sm text-gray-500">Apparaît en bas de chaque email envoyé depuis GesEnt.</p>
                    <textarea name="mail_signature" rows="5"
                              class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                              placeholder="Cordialement,&#10;Votre nom&#10;Téléphone : …">{{ old('mail_signature', $parametres->mail_signature) }}</textarea>
                </div>

                {{-- Templates email --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Modèles d'email</h2>
                    <p class="text-sm text-gray-500">
                        Variables disponibles : <code class="bg-gray-100 px-1 rounded">{client}</code>,
                        <code class="bg-gray-100 px-1 rounded">{numero}</code>,
                        <code class="bg-gray-100 px-1 rounded">{montant}</code>,
                        <code class="bg-gray-100 px-1 rounded">{entreprise}</code>,
                        <code class="bg-gray-100 px-1 rounded">{echeance}</code>,
                        <code class="bg-gray-100 px-1 rounded">{validite}</code>
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Devis</label>
                        <textarea name="mail_template_devis" rows="4"
                                  class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                                  placeholder="Laisser vide pour utiliser le modèle par défaut">{{ old('mail_template_devis', $parametres->mail_template_devis) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facture</label>
                        <textarea name="mail_template_facture" rows="4"
                                  class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                                  placeholder="Laisser vide pour utiliser le modèle par défaut">{{ old('mail_template_facture', $parametres->mail_template_facture) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bon de commande</label>
                        <textarea name="mail_template_bdc" rows="4"
                                  class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                                  placeholder="Laisser vide pour utiliser le modèle par défaut">{{ old('mail_template_bdc', $parametres->mail_template_bdc) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Relance (automatique)</label>
                        <textarea name="mail_template_relance" rows="4"
                                  class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
                                  placeholder="Laisser vide pour utiliser le modèle par défaut">{{ old('mail_template_relance', $parametres->mail_template_relance) }}</textarea>
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════
                 Onglet SÉCURITÉ
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'securite'" x-cloak class="space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Authentification à deux facteurs</h2>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="deux_facteurs_obligatoires" value="1"
                               class="mt-0.5 rounded border-gray-300 text-blue-600"
                               {{ old('deux_facteurs_obligatoires', $parametres->deux_facteurs_obligatoires) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium text-gray-900">Rendre la 2FA obligatoire</span>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Oblige tous les utilisateurs à configurer l'authentification à deux facteurs
                                avant d'accéder à l'application.
                            </p>
                        </div>
                    </label>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
                        <strong>Méthodes disponibles</strong> : Application TOTP (Google Authenticator, Authy…),
                        code par email, codes de récupération à usage unique.
                        Chaque utilisateur peut configurer sa 2FA depuis son profil.
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════
                 Onglet RH & Formation
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'rh'" x-cloak class="space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Organisme Paritaire de Formation (OPC)</h2>
                    <p class="text-xs text-gray-500">
                        L'OPC est l'organisme sectoriel auprès duquel l'entreprise est affiliée pour la formation des ouvriers de la construction (CP124).
                        Ces informations sont utilisées dans les documents RH et pour le suivi des certifications.
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organisme paritaire</label>
                            <select name="opc"
                                    class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">— Non défini —</option>
                                @foreach(\App\Models\ParametresEntreprise::OPC_LIST as $key => $label)
                                    <option value="{{ $key }}" @selected(old('opc', $parametres->opc) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° d'affiliation OPC</label>
                            <input type="text" name="opc_numero_affiliation"
                                   value="{{ old('opc_numero_affiliation', $parametres->opc_numero_affiliation) }}"
                                   placeholder="ex: BX-2026-00123"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                </div>{{-- /bg-white OPC --}}

                {{-- Calcul de rentabilité --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Calcul de rentabilité</h2>
                    <p class="text-xs text-gray-500">
                        Les frais généraux (personnel indirect + charges fixes) sont répartis sur les chantiers selon la méthode choisie.
                        Ce paramètre affecte la colonne "Marge nette" dans les statistiques et les fiches chantier.
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Clé de répartition des frais généraux</label>
                        @foreach(\App\Models\ParametresEntreprise::CLES_REPARTITION as $key => $label)
                        <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer mb-2
                            {{ old('cle_repartition_frais', $parametres->cle_repartition_frais ?? 'prorata_heures') === $key
                                ? 'border-blue-400 bg-blue-50'
                                : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" name="cle_repartition_frais" value="{{ $key }}"
                                   @checked(old('cle_repartition_frais', $parametres->cle_repartition_frais ?? 'prorata_heures') === $key)
                                   class="mt-0.5 text-blue-600">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                        <p class="text-xs text-gray-400 mt-2">
                            Gérez vos charges fixes dans
                            <a href="{{ route('charges-fonctionnement.index') }}" class="text-blue-500 hover:underline">Charges fixes &amp; frais généraux</a>.
                        </p>
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════
                 Onglet CATALOGUE
                 ════════════════════════════════════ --}}
            <div x-show="onglet === 'catalogue'" class="space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">Détection de volatilité catalogue</h2>

                    {{-- Toggle global --}}
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="volatilite_active" value="1"
                                   @checked(old('volatilite_active', $parametres->volatilite_active ?? true))
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <div>
                            <span class="text-sm font-medium text-gray-700">Module de volatilité actif</span>
                            <p class="text-xs text-gray-400">Désactiver n'efface pas les données calculées — elles restent informatives.</p>
                        </div>
                    </div>

                    {{-- Section Calcul --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 mb-3">Calcul</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fenêtre d'analyse</label>
                                <select name="volatilite_fenetre_mois"
                                        class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    @foreach([12 => '12 mois (1 an)', 24 => '24 mois (2 ans)', 36 => '36 mois (3 ans)', 60 => '60 mois (5 ans)'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('volatilite_fenetre_mois', $parametres->volatilite_fenetre_mois ?? 24) == $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Période analysée pour calculer les indicateurs.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Changements minimum pour classifier</label>
                                <input type="number" name="volatilite_min_changements_pour_classer"
                                       value="{{ old('volatilite_min_changements_pour_classer', $parametres->volatilite_min_changements_pour_classer ?? 3) }}"
                                       min="1" max="50" placeholder="3"
                                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <p class="text-xs text-gray-400 mt-1">Sous ce seuil → classe "insuffisant" (silencieux).</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section Classes --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 mb-3">Seuils de classification</h3>
                        <div class="space-y-4">

                            {{-- Stable --}}
                            <div class="bg-green-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-green-700 mb-2">Classe STABLE — amplitude faible</p>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amplitude max pour "stable" (%)</label>
                                    <input type="number" name="volatilite_seuil_stable_amplitude_pct" step="0.1"
                                           value="{{ old('volatilite_seuil_stable_amplitude_pct', $parametres->volatilite_seuil_stable_amplitude_pct ?? 2) }}"
                                           placeholder="2.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                    <p class="text-xs text-gray-400 mt-1">Si (max-min)/moy × 100 est inférieur à ce seuil, le produit est stable.</p>
                                </div>
                            </div>

                            {{-- Classe a --}}
                            <div class="bg-yellow-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-yellow-700 mb-2">Classe A — anomalie isolée récente</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Variation récente déclenchante (%)</label>
                                        <input type="number" name="volatilite_seuil_a_variation_pct" step="0.1"
                                               value="{{ old('volatilite_seuil_a_variation_pct', $parametres->volatilite_seuil_a_variation_pct ?? 8) }}"
                                               placeholder="8.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">|variation| >= ce % dans les 3 derniers mois.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Changements anciens max</label>
                                        <input type="number" name="volatilite_seuil_a_max_changements_anciens"
                                               value="{{ old('volatilite_seuil_a_max_changements_anciens', $parametres->volatilite_seuil_a_max_changements_anciens ?? 3) }}"
                                               placeholder="3" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">Historique "calme" avant les 3 derniers mois.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Classe b --}}
                            <div class="bg-orange-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-orange-700 mb-2">Classe B — augmentation/baisse constante</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pente annuelle déclenchante (%)</label>
                                        <input type="number" name="volatilite_seuil_b_pente_annuelle_pct" step="0.1"
                                               value="{{ old('volatilite_seuil_b_pente_annuelle_pct', $parametres->volatilite_seuil_b_pente_annuelle_pct ?? 10) }}"
                                               placeholder="10.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">|tendance 12m| >= ce % avec bonne régularité.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">R² minimum (régularité)</label>
                                        <input type="number" name="volatilite_seuil_b_r2_min" step="0.01" min="0" max="1"
                                               value="{{ old('volatilite_seuil_b_r2_min', $parametres->volatilite_seuil_b_r2_min ?? 0.700) }}"
                                               placeholder="0.700" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">Coefficient de détermination R² de la tendance linéaire (0 à 1).</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Classe c --}}
                            <div class="bg-red-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-red-700 mb-2">Classe C — yoyo structurel</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nb changements déclenchant</label>
                                        <input type="number" name="volatilite_seuil_c_nb_changements"
                                               value="{{ old('volatilite_seuil_c_nb_changements', $parametres->volatilite_seuil_c_nb_changements ?? 4) }}"
                                               placeholder="4" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">Nombre de changements sur la fenêtre complète.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Amplitude déclenchante (%)</label>
                                        <input type="number" name="volatilite_seuil_c_amplitude_pct" step="0.1"
                                               value="{{ old('volatilite_seuil_c_amplitude_pct', $parametres->volatilite_seuil_c_amplitude_pct ?? 10) }}"
                                               placeholder="10.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                        <p class="text-xs text-gray-400 mt-1">(max-min)/moy × 100 >= ce % pour yoyo.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section Signaux d'alerte --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 mb-3">Signaux d'alerte</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Garde-fou absolu (%)</label>
                                <input type="number" name="volatilite_garde_fou_absolu_pct" step="0.1"
                                       value="{{ old('volatilite_garde_fou_absolu_pct', $parametres->volatilite_garde_fou_absolu_pct ?? 15) }}"
                                       placeholder="15.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <p class="text-xs text-gray-400 mt-1">|tendance 12m| >= ce % → signal absolu, même si tout le secteur monte ensemble.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Écart vs groupe déclenchant (%)</label>
                                <input type="number" name="volatilite_signal_relatif_ecart_pct" step="0.1"
                                       value="{{ old('volatilite_signal_relatif_ecart_pct', $parametres->volatilite_signal_relatif_ecart_pct ?? 5) }}"
                                       placeholder="5.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                <p class="text-xs text-gray-400 mt-1">|tendance - médiane du groupe| >= ce % → signal relatif.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section Affichage devis --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 mb-3">Affichage en saisie de devis</h3>
                        <div class="max-w-xs">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Seuil de pertinence par ligne (€)</label>
                            <input type="number" name="volatilite_seuil_ligne_devis_eur" step="1" min="0"
                                   value="{{ old('volatilite_seuil_ligne_devis_eur', $parametres->volatilite_seuil_ligne_devis_eur ?? 200) }}"
                                   placeholder="200.00" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            <p class="text-xs text-gray-400 mt-1">Un badge volatilité n'apparaîtra que si le montant de la ligne dépasse ce seuil (utilisé dans la prochaine version).</p>
                        </div>
                    </div>

                    {{-- Bouton recalcul --}}
                    <div class="border-t pt-4">
                        <form method="POST" action="{{ route('parametres.recalculer-volatilite') }}"
                              onsubmit="return confirm('Le recalcul peut prendre une à deux minutes selon la taille du catalogue. Continuer ?')">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                                Recalculer tout le catalogue maintenant
                            </button>
                            <p class="text-xs text-gray-400 mt-1">Lance le moteur de volatilité sur l'ensemble du catalogue (2 passes). Opération synchrone.</p>
                        </form>
                    </div>
                </div>
            </div>{{-- /onglet catalogue --}}

            {{-- ════ Bouton sauvegarder (toujours visible) ════ --}}
            <div class="flex justify-end mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Sauvegarder
                </button>
            </div>

        </div>{{-- /x-data onglets --}}
    </form>

<script>
function smtpPresets() {
    return {
        presets: [
            { label: 'Gmail',      host: 'smtp.gmail.com',      port: 587, encryption: 'tls' },
            { label: 'Outlook',    host: 'smtp.office365.com',  port: 587, encryption: 'tls' },
            { label: 'OVH',        host: 'ssl0.ovh.net',        port: 465, encryption: 'ssl' },
            { label: 'Ionos',      host: 'smtp.ionos.fr',       port: 587, encryption: 'tls' },
            { label: 'Mailgun EU', host: 'smtp.eu.mailgun.org', port: 587, encryption: 'tls' },
            { label: 'Brevo',      host: 'smtp-relay.brevo.com',port: 587, encryption: 'tls' },
        ],
        appliquer(preset) {
            document.getElementById('mail_host').value       = preset.host;
            document.getElementById('mail_port').value       = preset.port;
            document.getElementById('mail_encryption').value = preset.encryption;
        },
    };
}
</script>
</x-app-layout>
