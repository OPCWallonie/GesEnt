<x-app-layout>
    <x-slot name="header">Notifications</x-slot>

    <x-slot name="actions">
        @if(auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('notifications.marquer-toutes-lues') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Tout marquer comme lu
            </button>
        </form>
        @endif
    </x-slot>

    <div class="max-w-3xl">
        @if($notifications->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p class="font-medium">Aucune notification</p>
                <p class="text-sm mt-1">Vous êtes à jour !</p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                @foreach($notifications as $notif)
                @php
                    $data  = $notif->data;
                    $type  = $data['type'] ?? 'info';
                    $titre = $data['titre'] ?? 'Notification';
                    $msg   = $data['message'] ?? '';
                    $url   = $data['url'] ?? null;
                    $colors = [
                        'facture_en_retard' => ['bg' => 'bg-red-100',    'text' => 'text-red-600',    'dot' => 'bg-red-400'],
                        'devis_expire'      => ['bg' => 'bg-amber-100',  'text' => 'text-amber-600',  'dot' => 'bg-amber-400'],
                        'journal_chantier'  => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'dot' => 'bg-blue-400'],
                    ];
                    $c = $colors[$type] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'dot' => 'bg-gray-400'];
                @endphp
                <div class="flex items-start gap-4 p-4 {{ $notif->read_at ? 'bg-white' : 'bg-blue-50/40' }}">
                    <div class="mt-1">
                        <span class="inline-block w-2 h-2 rounded-full {{ $notif->read_at ? 'bg-gray-200' : $c['dot'] }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $titre }}</p>
                                <p class="text-sm text-gray-600 mt-0.5">{{ $msg }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($url)
                                <a href="{{ route('notifications.redirect', $notif->id) }}"
                                   class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Voir
                                </a>
                                @endif
                                <form method="POST" action="{{ route('notifications.destroy', $notif->id) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-300 hover:text-red-400" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>
