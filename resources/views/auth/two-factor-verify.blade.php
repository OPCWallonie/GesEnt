<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Vérification en deux étapes</h2>
        <p class="text-sm text-gray-600 mt-1">Choisissez une méthode de vérification.</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif
    @if(session('email_sent'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('email_sent') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
            {{ session('warning') }}
        </div>
    @endif

    <div x-data="{ onglet: 'totp' }">

        {{-- Onglets --}}
        <div class="flex border-b border-gray-200 mb-5 gap-0">
            <button type="button" @click="onglet = 'totp'"
                    :class="onglet === 'totp' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium -mb-px">
                Application
            </button>
            <button type="button" @click="onglet = 'email'"
                    :class="onglet === 'email' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium -mb-px">
                Email
            </button>
            <button type="button" @click="onglet = 'recovery'"
                    :class="onglet === 'recovery' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium -mb-px">
                Code de récupération
            </button>
        </div>

        {{-- Onglet TOTP --}}
        <div x-show="onglet === 'totp'">
            <p class="text-sm text-gray-500 mb-4">
                Entrez le code à 6 chiffres généré par votre application d'authentification.
            </p>
            <form method="POST" action="{{ route('2fa.verify.check') }}">
                @csrf
                <div class="mb-4">
                    <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           required autofocus autocomplete="one-time-code"
                           placeholder="000000"
                           class="w-full rounded-lg border-gray-300 text-center text-2xl tracking-widest font-mono">
                </div>
                <button type="submit"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Vérifier
                </button>
            </form>
        </div>

        {{-- Onglet Email --}}
        <div x-show="onglet === 'email'" x-cloak>
            <p class="text-sm text-gray-500 mb-4">
                Recevez un code à 6 chiffres par email. Valable 10 minutes.
            </p>

            {{-- Bouton envoyer code --}}
            <form method="POST" action="{{ route('2fa.email-code') }}" class="mb-4">
                @csrf
                <button type="submit"
                        class="w-full px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 font-medium text-sm">
                    Envoyer un code par email
                </button>
            </form>

            {{-- Formulaire vérification email --}}
            <form method="POST" action="{{ route('2fa.verify.check') }}">
                @csrf
                <input type="hidden" name="via_email" value="1">
                <div class="mb-4">
                    <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           required autocomplete="one-time-code"
                           placeholder="000000"
                           class="w-full rounded-lg border-gray-300 text-center text-2xl tracking-widest font-mono">
                </div>
                <button type="submit"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Vérifier le code email
                </button>
            </form>
        </div>

        {{-- Onglet Code de récupération --}}
        <div x-show="onglet === 'recovery'" x-cloak>
            <p class="text-sm text-gray-500 mb-4">
                Entrez l'un de vos codes de récupération (format <code class="font-mono bg-gray-100 px-1 rounded">XXXXX-XXXXX</code>).
                Chaque code ne peut être utilisé qu'une seule fois.
            </p>
            <form method="POST" action="{{ route('2fa.verify.check') }}">
                @csrf
                <div class="mb-4">
                    <input type="text" name="code" maxlength="13"
                           required autocomplete="off"
                           placeholder="XXXXX-XXXXX"
                           class="w-full rounded-lg border-gray-300 text-center text-xl tracking-widest font-mono uppercase">
                </div>
                <button type="submit"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Utiliser ce code
                </button>
            </form>
        </div>

    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
            Se déconnecter
        </button>
    </form>
</x-guest-layout>
