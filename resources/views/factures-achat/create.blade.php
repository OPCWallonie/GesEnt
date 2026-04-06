<x-app-layout>
    <x-slot name="header">Nouvelle facture achat</x-slot>

    @php
        $aiActive = \App\Models\ParametresEntreprise::instance()->aiConfiguree();
    @endphp

    {{-- Bouton OCR (si IA configurée) --}}
    @if($aiActive)
    <div x-data="ocrImport()" class="mb-4">
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl p-4 flex items-center gap-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-indigo-900">Importer depuis un PDF ou une photo</p>
                <p class="text-xs text-indigo-600 mt-0.5">L'IA extraira automatiquement les montants, dates et numéro de facture.</p>
            </div>
            <label class="cursor-pointer">
                <input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="hidden" @change="analyser($event)">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-text="label">Choisir un fichier</span>
                </span>
            </label>
        </div>

        {{-- Barre de progression --}}
        <div x-show="chargement" x-cloak class="mt-2 flex items-center gap-3 text-sm text-indigo-700">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Analyse en cours…
        </div>

        {{-- Erreur --}}
        <div x-show="erreur" x-cloak class="mt-2 bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm text-red-700" x-text="erreur"></div>

        {{-- Résumé du remplissage --}}
        <div x-show="succes" x-cloak class="mt-2 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
            Formulaire pré-rempli avec les données extraites. Vérifiez et complétez si nécessaire.
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('factures-achat.store') }}" class="space-y-6" id="form-facture-achat">
        @csrf
        {{-- Marquage automatique source OCR --}}
        <input type="hidden" name="from_ocr" id="from_ocr_flag" value="0">
        @php $facture = null; @endphp
        @include('factures-achat._form')
        <div class="flex justify-between">
            <a href="{{ route('factures-achat.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Enregistrer
            </button>
        </div>
    </form>

    @if($aiActive)
    <script>
    function ocrImport() {
        return {
            label: 'Choisir un fichier',
            chargement: false,
            erreur: null,
            succes: false,

            async analyser(event) {
                const fichier = event.target.files[0];
                if (!fichier) return;

                this.label     = fichier.name;
                this.chargement = true;
                this.erreur    = null;
                this.succes    = false;

                const formData = new FormData();
                formData.append('fichier', fichier);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                try {
                    const res  = await fetch('{{ route('ocr.extract') }}', { method: 'POST', body: formData });
                    const json = await res.json();

                    if (!json.success) {
                        this.erreur = json.message || "Erreur lors de l'analyse.";
                        return;
                    }

                    this.remplir(json.data);
                    this.succes = true;
                    document.getElementById('from_ocr_flag').value = '1';

                } catch (e) {
                    this.erreur = "Impossible de contacter le serveur.";
                } finally {
                    this.chargement = false;
                }
            },

            remplir(data) {
                const set = (name, value) => {
                    if (value === null || value === undefined) return;
                    const el = document.querySelector(`[name="${name}"]`);
                    if (!el) return;
                    el.value = value;
                    // Déclencher les événements Alpine si nécessaire
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                };

                if (data.numero_facture)  set('reference_fournisseur', data.numero_facture);
                if (data.date_document)   set('date_document', data.date_document);
                if (data.date_echeance)   set('date_echeance', data.date_echeance);
                if (data.montant_ht)      set('montant_ht', data.montant_ht);
                if (data.taux_tva)        set('taux_tva', data.taux_tva);
                if (data.notes)           set('notes', data.notes);

                // Si un fournisseur est trouvé, chercher dans la liste
                if (data.fournisseur_nom) {
                    const select = document.querySelector('[name="fournisseur_id"]');
                    if (select) {
                        const nom = data.fournisseur_nom.toLowerCase();
                        for (const opt of select.options) {
                            if (opt.text.toLowerCase().includes(nom)) {
                                select.value = opt.value;
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                                break;
                            }
                        }
                    }
                }
            }
        };
    }
    </script>
    @endif
</x-app-layout>
