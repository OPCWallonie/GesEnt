<x-app-layout>
    <x-slot name="header">Modifier — {{ $facture->numero }}</x-slot>

    <form method="POST" action="{{ route('factures-achat.update', $facture) }}" class="space-y-6" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('factures-achat._form')
        <div class="flex justify-between">
            <a href="{{ route('factures-achat.show', $facture) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">← Retour</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Enregistrer
            </button>
        </div>
    </form>
</x-app-layout>
