<x-app-layout>
    <x-slot name="header">Nouvelle facture achat</x-slot>

    @php
        $aiActive = \App\Models\ParametresEntreprise::instance()->aiConfiguree();
    @endphp

    <div x-data="ocrImport()" class="flex gap-6 items-start">

        {{-- ───── Panneau PDF / image viewer ───── --}}
        <div x-show="viewerUrl" x-cloak
             class="w-1/2 sticky top-4 self-start rounded-xl shadow-sm border border-gray-200 bg-white overflow-hidden flex flex-col"
             style="height: calc(100vh - 7rem)">

            {{-- Barre titre viewer --}}
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50 shrink-0">
                <span class="text-sm font-medium text-gray-700 truncate" x-text="nomFichier"></span>
                <button type="button" @click="fermerViewer()"
                        title="Fermer le viewer"
                        class="ml-2 shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Contenu viewer --}}
            <div class="flex-1 overflow-hidden">
                <template x-if="viewerType === 'image'">
                    <img :src="viewerUrl" class="w-full h-full object-contain p-4"
                         style="max-height: calc(100vh - 11rem)">
                </template>
                <template x-if="viewerType === 'pdf'">
                    <iframe :src="viewerUrl" class="w-full border-0"
                            style="height: calc(100vh - 11rem)"></iframe>
                </template>
            </div>
        </div>

        {{-- ───── Panneau formulaire ───── --}}
        <div :class="viewerUrl ? 'w-1/2' : 'w-full'" class="space-y-6">

            {{-- Bouton OCR (si IA configurée) --}}
            @if($aiActive)
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
            <div x-show="chargement" x-cloak class="flex items-center gap-3 text-sm text-indigo-700 -mt-2">
                <svg class="animate-spin w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Analyse en cours…
            </div>

            {{-- Erreur --}}
            <div x-show="erreur" x-cloak
                 class="-mt-2 bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm text-red-700"
                 x-text="erreur"></div>

            {{-- Succès --}}
            <div x-show="succes" x-cloak
                 class="-mt-2 bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
                Formulaire pré-rempli avec les données extraites. Vérifiez et complétez si nécessaire.
            </div>
            @endif

            <form method="POST" action="{{ route('factures-achat.store') }}" id="form-facture-achat">
                @csrf
                <input type="hidden" name="from_ocr" id="from_ocr_flag" value="0">
                @php $facture = null; @endphp

                <div class="space-y-6">
                    @include('factures-achat._form')

                    <div class="flex justify-between">
                        <a href="{{ route('factures-achat.index') }}"
                           class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>

        </div>{{-- /panneau formulaire --}}
    </div>

<script>
function ocrImport() {
    return {
        label: 'Choisir un fichier',
        nomFichier: '',
        chargement: false,
        erreur: null,
        succes: false,
        viewerUrl: null,
        viewerType: null,

        fermerViewer() {
            if (this.viewerUrl) URL.revokeObjectURL(this.viewerUrl);
            this.viewerUrl  = null;
            this.viewerType = null;
        },

        async analyser(event) {
            const fichier = event.target.files[0];
            if (!fichier) return;

            this.nomFichier = fichier.name;
            this.label      = fichier.name;

            // Afficher le viewer immédiatement
            if (this.viewerUrl) URL.revokeObjectURL(this.viewerUrl);
            this.viewerUrl  = URL.createObjectURL(fichier);
            this.viewerType = fichier.type.startsWith('image/') ? 'image' : 'pdf';

            this.chargement = true;
            this.erreur     = null;
            this.succes     = false;

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
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            };

            if (data.numero_facture)  set('reference_fournisseur', data.numero_facture);
            if (data.date_document)   set('date_document', data.date_document);
            if (data.date_echeance)   set('date_echeance', data.date_echeance);
            if (data.montant_ht)      set('montant_ht', data.montant_ht);
            if (data.taux_tva)        set('taux_tva', data.taux_tva);
            if (data.notes)           set('notes', data.notes);

            if (data.fournisseur_nom) {
                fetch('{{ route('fournisseurs.api-search') }}?q=' + encodeURIComponent(data.fournisseur_nom), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(results => {
                    if (results.length > 0) {
                        window.dispatchEvent(new CustomEvent('combobox-add-item', {
                            detail: { field: 'fournisseur_id', id: results[0].id, nom: results[0].nom }
                        }));
                    }
                });
            }
        },
    };
}
</script>
</x-app-layout>
