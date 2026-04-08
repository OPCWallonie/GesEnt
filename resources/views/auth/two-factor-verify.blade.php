<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Vérification en deux étapes</h2>
        <p class="text-sm text-gray-600 mt-1">
            Entrez le code à 6 chiffres de votre application d'authentification.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.verify.check') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Code de vérification</label>
            <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
                   autofocus autocomplete="one-time-code"
                   placeholder="000000"
                   class="w-full rounded-lg border-gray-300 text-center text-2xl tracking-widest font-mono">
            @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            Vérifier
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
            Se déconnecter
        </button>
    </form>
</x-guest-layout>
