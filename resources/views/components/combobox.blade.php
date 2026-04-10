@props([
    'name'         => 'field',
    'label'        => null,
    'endpoint'     => '',
    'value'        => null,
    'text'         => '',
    'required'     => false,
    'placeholder'  => 'Rechercher…',
    'canClear'     => true,
    'allowCreate'  => false,
    'createLabel'  => 'Créer',
    'createUrl'    => null,
    'createFields' => [],
])

@php
    $uid          = 'cb_' . str_replace(['-','[',']','.'], '_', $name) . '_' . substr(md5($name . $endpoint), 0, 6);
    $createFields = is_array($createFields) ? $createFields : [];
    $configJson   = json_encode([
        'fieldName'    => $name,
        'endpoint'     => $endpoint,
        'initialId'    => $value,
        'initialText'  => $text,
        'allowCreate'  => $allowCreate,
        'createLabel'  => $createLabel,
        'createUrl'    => $createUrl,
        'createFields' => $createFields,
    ], JSON_UNESCAPED_UNICODE);
@endphp

<div x-data="comboboxWidget({{ $configJson }})"
     x-init="init()"
     class="relative"
     @click.outside="showDropdown = false; showMiniForm = false">

    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}{{ $required ? ' *' : '' }}
        </label>
    @endif

    {{-- Hidden input carrying the selected ID --}}
    <input type="hidden"
           name="{{ $name }}"
           :value="selectedId ?? ''"
           {{ $required ? 'x-bind:required="!selectedId"' : '' }}>

    {{-- Visible typeahead input --}}
    <div class="relative flex items-center">
        <input type="text"
               x-model="query"
               @input.debounce.250ms="allLoaded = false; search()"
               @focus="search()"
               @keydown.arrow-down.prevent="highlightNext()"
               @keydown.arrow-up.prevent="highlightPrev()"
               @keydown.enter.prevent="selectHighlighted()"
               @keydown.escape="showDropdown = false; showMiniForm = false"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               {{ $required ? 'x-bind:required="!selectedId"' : '' }}
               class="w-full rounded-lg border-gray-300 shadow-sm text-sm pr-8 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has($name) ? 'border-red-400' : '' }}">

        {{-- Spinner --}}
        <span x-show="loading" x-cloak class="absolute right-8 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="animate-spin w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </span>

        {{-- Clear button --}}
        @if($canClear)
        <button type="button"
                x-show="selectedId"
                x-cloak
                @click="clear()"
                tabindex="-1"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        @endif
    </div>

    {{-- Dropdown results --}}
    <div x-show="showDropdown && (results.length > 0 || allowCreate)"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-y-auto text-sm">

        {{-- Search results --}}
        <template x-for="(item, idx) in results" :key="item.id">
            <div @click="select(item)"
                 @mouseenter="highlighted = idx"
                 :class="highlighted === idx ? 'bg-blue-50 text-blue-900' : 'text-gray-800'"
                 class="px-3 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-900">
                <span x-text="item.nom"></span>
                <span x-show="item.sous_texte" x-cloak class="text-xs text-gray-400 ml-1" x-text="'— ' + item.sous_texte"></span>
            </div>
        </template>

        {{-- "No results" message (when not showing create) --}}
        <div x-show="results.length === 0 && !allowCreate && !loading"
             class="px-3 py-2 text-gray-400 text-xs italic">
            Aucun résultat
        </div>

        {{-- "Voir tout" button (hidden once all are shown) --}}
        <div x-show="!allLoaded && results.length > 0"
             x-cloak
             @click.stop="loadAll()"
             class="px-3 py-1.5 text-center text-xs text-gray-400 hover:text-gray-600 cursor-pointer border-t border-gray-100 hover:bg-gray-50">
            ···
        </div>

        {{-- Create option --}}
        @if($allowCreate)
        <div @click="clickCreate()"
             @mouseenter="highlighted = results.length"
             :class="highlighted === results.length ? 'bg-green-50 text-green-800' : 'text-green-700'"
             class="px-3 py-2 cursor-pointer border-t border-gray-100 hover:bg-green-50 flex items-center gap-1.5 font-medium">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>{{ $createLabel }}<span x-show="query.length > 0" x-cloak> « <span x-text="query"></span> »</span></span>
        </div>
        @endif
    </div>

    {{-- Inline mini-form (shown below input when createUrl is set) --}}
    @if($allowCreate && $createUrl)
    <div x-show="showMiniForm"
         x-cloak
         x-transition
         class="mt-2 bg-white border border-green-200 rounded-lg shadow p-4 space-y-3 text-sm">
        <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">{{ $createLabel }}</p>

        @foreach($createFields as $field)
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
                {{ $field['label'] ?? $field['name'] }}{{ !empty($field['required']) ? ' *' : '' }}
            </label>
            <input type="{{ $field['type'] ?? 'text' }}"
                   x-model="miniFormData['{{ $field['name'] }}']"
                   {{ !empty($field['required']) ? 'required' : '' }}
                   class="w-full rounded border-gray-300 shadow-sm text-sm">
        </div>
        @endforeach

        <div class="flex justify-end gap-2 pt-1">
            <button type="button"
                    @click="showMiniForm = false"
                    class="px-3 py-1.5 text-xs text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                Annuler
            </button>
            <button type="button"
                    @click="submitMiniForm()"
                    :disabled="miniFormLoading"
                    class="px-3 py-1.5 text-xs bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                <span x-show="!miniFormLoading">Créer</span>
                <span x-show="miniFormLoading" x-cloak>…</span>
            </button>
        </div>
        <p x-show="miniFormError" x-cloak class="text-xs text-red-600" x-text="miniFormError"></p>
    </div>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
if (typeof comboboxWidget === 'undefined') {
    function comboboxWidget(config) {
        return {
            fieldName:      config.fieldName,
            endpoint:       config.endpoint,
            query:          config.initialText || '',
            selectedId:     config.initialId   || null,
            selectedText:   config.initialText || '',
            results:        [],
            showDropdown:   false,
            loading:        false,
            highlighted:    -1,
            allLoaded:      false,
            allowCreate:    config.allowCreate  || false,
            createLabel:    config.createLabel  || 'Créer',
            createUrl:      config.createUrl    || null,
            createFields:   config.createFields || [],
            showMiniForm:   false,
            miniFormData:   {},
            miniFormLoading:false,
            miniFormError:  null,

            init() {
                window.addEventListener('combobox-update-endpoint', (e) => {
                    if (e.detail.field === this.fieldName) {
                        this.endpoint = e.detail.endpoint;
                        this.clear();
                    }
                });
                window.addEventListener('combobox-update-create-url', (e) => {
                    if (e.detail.field === this.fieldName) {
                        this.createUrl = e.detail.createUrl;
                    }
                });
                window.addEventListener('combobox-add-item', (e) => {
                    if (e.detail.field === this.fieldName) {
                        this.select({ id: e.detail.id, nom: e.detail.nom });
                    }
                });
                window.addEventListener('combobox-trigger-search', (e) => {
                    if (e.detail.field === this.fieldName) {
                        this.search();
                    }
                });
            },

            async search(allResults) {
                this.allLoaded = !!allResults;
                this.loading = true;
                this.showDropdown = true;
                try {
                    const sep = this.endpoint.includes('?') ? '&' : '?';
                    const url = this.endpoint + sep + 'q=' + encodeURIComponent(this.query)
                              + (allResults ? '&all=1' : '');
                    const r = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.results = await r.json();
                } catch(e) {
                    this.results = [];
                }
                this.loading = false;
                this.highlighted = -1;
            },

            loadAll() {
                this.search(true);
            },

            select(item) {
                this.selectedId   = item.id;
                this.selectedText = item.nom;
                this.query        = item.nom;
                this.showDropdown = false;
                this.results      = [];
                this.showMiniForm = false;
                window.dispatchEvent(new CustomEvent('combobox-selected', {
                    detail: { field: this.fieldName, id: item.id, nom: item.nom, item }
                }));
            },

            clear() {
                this.selectedId   = null;
                this.selectedText = '';
                this.query        = '';
                this.showDropdown = false;
                this.results      = [];
                window.dispatchEvent(new CustomEvent('combobox-cleared', {
                    detail: { field: this.fieldName }
                }));
            },

            clickCreate() {
                if (this.createUrl) {
                    this.miniFormData  = { nom: this.query };
                    this.miniFormError = null;
                    this.showMiniForm  = true;
                    this.showDropdown  = false;
                } else {
                    this.showDropdown = false;
                    window.dispatchEvent(new CustomEvent('combobox-create', {
                        detail: { field: this.fieldName, query: this.query }
                    }));
                }
            },

            async submitMiniForm() {
                this.miniFormLoading = true;
                this.miniFormError   = null;
                try {
                    const resp = await fetch(this.createUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(this.miniFormData),
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.miniFormError = data.message || 'Erreur lors de la création.';
                    } else {
                        this.showMiniForm = false;
                        this.select({ id: data.id, nom: data.nom });
                    }
                } catch(e) {
                    this.miniFormError = 'Erreur réseau.';
                }
                this.miniFormLoading = false;
            },

            highlightNext() {
                const max = this.results.length - 1 + (this.allowCreate ? 1 : 0);
                this.highlighted = Math.min(this.highlighted + 1, max);
            },
            highlightPrev() {
                this.highlighted = Math.max(this.highlighted - 1, -1);
            },
            selectHighlighted() {
                if (this.highlighted === -1) return;
                if (this.allowCreate && this.highlighted === this.results.length) {
                    this.clickCreate();
                } else if (this.results[this.highlighted]) {
                    this.select(this.results[this.highlighted]);
                }
            },
        };
    }
}
</script>
