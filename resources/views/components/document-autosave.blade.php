@props([
    'documentType' => 'devis',
    'documentId'   => null,
])

<div x-data="documentAutosave({
        documentType: '{{ $documentType }}',
        documentId: {{ $documentId ?? 'null' }},
     })"
     x-init="init()"
     class="contents">

    {{ $slot }}

    {{-- Indicateur d'état de sauvegarde (coin inférieur droit) --}}
    <div class="fixed bottom-4 right-4 z-40 pointer-events-none"
         x-show="feedback !== null"
         x-transition.opacity>
        <div class="bg-white border rounded-lg shadow-sm px-3 py-2 text-xs flex items-center gap-2"
             :class="{
                 'border-green-200 text-green-700': feedback === 'saved',
                 'border-gray-200 text-gray-600': feedback === 'saving',
                 'border-red-200 text-red-700': feedback === 'error'
             }">
            <template x-if="feedback === 'saving'">
                <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </template>
            <template x-if="feedback === 'saved'">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </template>
            <span x-text="feedbackText"></span>
        </div>
    </div>

    {{-- Bandeau de restauration --}}
    <div x-show="draftDisponible" x-cloak x-transition
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 max-w-xl w-full mx-4">
        <div class="bg-amber-50 border border-amber-300 rounded-xl shadow-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 text-sm">
                    <p class="font-semibold text-amber-900">Brouillon non sauvegardé détecté</p>
                    <p class="text-amber-700 text-xs mt-1">
                        Enregistré il y a <span x-text="draftAgeLabel"></span>. Souhaitez-vous le restaurer ?
                    </p>
                    <div class="flex gap-2 mt-3">
                        <button type="button" @click="restaurerDraft()"
                                class="px-3 py-1.5 bg-amber-600 text-white text-xs rounded-lg hover:bg-amber-700 font-medium">
                            Restaurer le brouillon
                        </button>
                        <button type="button" @click="ignorerDraft()"
                                class="px-3 py-1.5 border border-amber-300 text-amber-800 text-xs rounded-lg hover:bg-amber-100">
                            Ignorer
                        </button>
                    </div>
                </div>
                <button @click="draftDisponible = false" class="text-amber-400 hover:text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof documentAutosave === 'undefined') {
    function documentAutosave(config) {
        return {
            documentType:    config.documentType,
            documentId:      config.documentId,
            storageKey:      `draft_${config.documentType}_${config.documentId || 'new'}`,
            feedback:        null,
            feedbackText:    '',
            draftDisponible: false,
            draftData:       null,
            draftAgeLabel:   '',
            formEl:          null,
            isDirty:         false,
            suppressSave:    false,

            async init() {
                this.formEl = this.$root.closest('form') || this.$root.querySelector('form');
                if (!this.formEl) return;

                // Ne pas proposer de draft si le form a déjà des valeurs (retour validation Laravel)
                const formHasValues = new FormData(this.formEl).has('client_id')
                    && this.formEl.querySelector('[name="client_id"]')?.value;

                if (!formHasValues) {
                    this.verifierDraftLocal();
                    await this.verifierDraftBdd();
                }

                this.formEl.addEventListener('input',  () => { if (!this.suppressSave) this.isDirty = true; });
                this.formEl.addEventListener('change', () => { if (!this.suppressSave) this.isDirty = true; });

                setInterval(() => { if (this.isDirty) this.sauvegarderLocal(); }, 30_000);
                setInterval(() => { if (this.isDirty) this.sauvegarderBdd(); }, 120_000);

                window.addEventListener('beforeunload', () => {
                    if (this.isDirty) this.sauvegarderLocal();
                });

                this.formEl.addEventListener('submit', () => {
                    this.suppressSave = true;
                    setTimeout(() => { if (this.suppressSave) this.clearLocal(); }, 5000);
                });
            },

            serialiserForm() {
                const fd = new FormData(this.formEl);
                const data = {};
                for (const [key, value] of fd.entries()) {
                    if (data[key] !== undefined) {
                        if (!Array.isArray(data[key])) data[key] = [data[key]];
                        data[key].push(value);
                    } else {
                        data[key] = value;
                    }
                }
                return data;
            },

            sauvegarderLocal() {
                try {
                    const snapshot = { data: this.serialiserForm(), saved_at: new Date().toISOString() };
                    localStorage.setItem(this.storageKey, JSON.stringify(snapshot));
                    this.isDirty = false;
                    this.afficherFeedback('saved', 'Enregistré localement');
                } catch (e) {}
            },

            async sauvegarderBdd() {
                this.afficherFeedback('saving', 'Sauvegarde…');
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const resp = await fetch('{{ route('drafts.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            document_type: this.documentType,
                            document_id:   this.documentId,
                            data:          this.serialiserForm(),
                        }),
                    });
                    if (resp.ok) {
                        this.afficherFeedback('saved', 'Sauvegardé');
                        this.isDirty = false;
                    } else {
                        this.afficherFeedback('error', 'Erreur sauvegarde');
                    }
                } catch (e) {
                    this.afficherFeedback('error', 'Hors ligne');
                }
            },

            verifierDraftLocal() {
                try {
                    const raw = localStorage.getItem(this.storageKey);
                    if (!raw) return;
                    const snapshot = JSON.parse(raw);
                    const ageMs = Date.now() - new Date(snapshot.saved_at).getTime();
                    if (ageMs > 48 * 3600 * 1000) { this.clearLocal(); return; }
                    this.draftData     = snapshot.data;
                    this.draftAgeLabel = this.formatAge(ageMs);
                    this.draftDisponible = true;
                } catch (e) { this.clearLocal(); }
            },

            async verifierDraftBdd() {
                if (this.draftDisponible) return;
                try {
                    const params = new URLSearchParams({ document_type: this.documentType });
                    if (this.documentId) params.set('document_id', this.documentId);
                    const resp = await fetch(`{{ route('drafts.load') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    if (json.draft) {
                        this.draftData     = json.draft.data;
                        this.draftAgeLabel = `${json.draft.age_minutes} min`;
                        this.draftDisponible = true;
                    }
                } catch (e) {}
            },

            restaurerDraft() {
                if (!this.draftData) return;
                this.suppressSave = true;

                // Champs simples (non-lignes)
                for (const [key, value] of Object.entries(this.draftData)) {
                    if (key.startsWith('lignes[')) continue;
                    const el = this.formEl.querySelector(`[name="${CSS.escape(key)}"]`);
                    if (!el) continue;
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        el.checked = el.value === value;
                    } else {
                        el.value = value;
                    }
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // Lignes Alpine : reconstruire depuis les données brutes du draft
                const lignesMap = {};
                for (const [key, value] of Object.entries(this.draftData)) {
                    const m = key.match(/^lignes\[(\d+)\]\[(\w+)\]$/);
                    if (!m) continue;
                    const idx = parseInt(m[1]);
                    const champ = m[2];
                    lignesMap[idx] ??= {};
                    lignesMap[idx][champ] = value;
                }
                const lignesRestaurees = Object.keys(lignesMap)
                    .sort((a, b) => a - b)
                    .map(i => lignesMap[i]);

                if (lignesRestaurees.length > 0) {
                    window.dispatchEvent(new CustomEvent('restaurer-lignes', { detail: { lignes: lignesRestaurees } }));
                }

                this.draftDisponible = false;
                this.afficherFeedback('saved', 'Brouillon restauré');
                setTimeout(() => { this.suppressSave = false; }, 500);
            },

            ignorerDraft() {
                this.clearLocal();
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                fetch('{{ route('drafts.destroy') }}', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ document_type: this.documentType, document_id: this.documentId }),
                }).catch(() => {});
                this.draftDisponible = false;
            },

            clearLocal() {
                try { localStorage.removeItem(this.storageKey); } catch (e) {}
            },

            afficherFeedback(type, text) {
                this.feedback = type;
                this.feedbackText = text;
                setTimeout(() => { this.feedback = null; }, 3000);
            },

            formatAge(ms) {
                const min = Math.floor(ms / 60000);
                if (min < 1)  return 'quelques secondes';
                if (min < 60) return `${min} min`;
                const h = Math.floor(min / 60);
                if (h < 24) return `${h}h${min % 60 > 0 ? ' ' + (min % 60) + 'min' : ''}`;
                return `${Math.floor(h / 24)} jour(s)`;
            },
        };
    }
}
</script>
