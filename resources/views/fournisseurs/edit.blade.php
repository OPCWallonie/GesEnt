<x-app-layout>
    <x-slot name="header">Modifier — {{ $fournisseur->nom }}</x-slot>

    <form method="POST" action="{{ route('fournisseurs.update', $fournisseur) }}" class="space-y-6 max-w-3xl">
        @csrf @method('PUT')
        @include('fournisseurs._form')
        <div class="flex justify-between">
            <a href="{{ route('fournisseurs.show', $fournisseur) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Enregistrer</button>
        </div>
    </form>
</x-app-layout>
