<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Activer l'authentification à deux facteurs</h2>
        <p class="text-sm text-gray-600 mt-1">
            Scannez ce QR code avec votre application d'authentification
            (Google Authenticator, Authy, Microsoft Authenticator…).
        </p>
    </div>

    <div class="flex justify-center mb-6 p-4 bg-white border border-gray-200 rounded-lg">
        {!! $qrCodeSvg !!}
    </div>

    <div class="mb-6 text-center">
        <p class="text-xs text-gray-500 mb-1">Ou entrez ce code manuellement dans votre application :</p>
        <code class="bg-gray-100 px-3 py-1.5 rounded font-mono text-sm tracking-widest">{{ $secret }}</code>
    </div>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.enable') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Code de vérification (6 chiffres)</label>
            <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
                   autofocus autocomplete="one-time-code"
                   placeholder="000000"
                   class="w-full rounded-lg border-gray-300 text-center text-2xl tracking-widest font-mono">
            @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            Activer la 2FA
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
    </div>
</x-guest-layout>
