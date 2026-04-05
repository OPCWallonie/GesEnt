<x-app-layout>
    <x-slot name="header">Nouvel utilisateur</x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf
            @include('users._form')
            <div class="flex justify-between">
                <a href="{{ route('users.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Créer
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
