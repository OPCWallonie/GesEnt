import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Composant recherche live (Ajax, debounce 300ms) pour les pages index
Alpine.data('liveSearch', () => ({
    loading: false,

    async doSearch(form) {
        this.loading = true;

        const data   = new FormData(form);
        const params = new URLSearchParams();

        for (const [k, v] of data.entries()) {
            if (v !== '') params.set(k, v);
        }

        const url = new URL(window.location.pathname, window.location.origin);
        url.search = params.toString();
        history.replaceState({}, '', url);

        try {
            const resp = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const html = await resp.text();
            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const src  = doc.getElementById('search-results');
            const dst  = document.getElementById('search-results');
            if (src && dst) dst.innerHTML = src.innerHTML;
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.start();
