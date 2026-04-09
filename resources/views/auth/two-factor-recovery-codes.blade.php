<x-guest-layout>
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">2FA activée avec succès !</h2>
        </div>
        <p class="text-sm text-gray-600">
            Conservez ces codes de récupération dans un endroit sûr.
            Chacun ne peut être utilisé qu'<strong>une seule fois</strong> pour accéder à votre compte si vous perdez accès à votre application d'authentification.
        </p>
    </div>

    @if(!empty($codes))
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-2 gap-2">
            @foreach($codes as $code)
                <code class="font-mono text-sm text-gray-800 bg-white border border-gray-200 rounded px-3 py-1.5 text-center tracking-widest">
                    {{ $code }}
                </code>
            @endforeach
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6 text-xs text-yellow-800">
        <strong>Important :</strong> Ces codes ne seront plus affichés. Copiez-les maintenant.
    </div>

    <button onclick="copierCodes()" type="button"
            class="w-full mb-3 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
        Copier tous les codes
    </button>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6 text-sm text-blue-800">
        La 2FA est déjà activée sur votre compte.
    </div>
    @endif

    <a href="{{ route('profile.edit') }}"
       class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm">
        Continuer vers le profil
    </a>

    @if(!empty($codes))
    <script>
    function copierCodes() {
        const codes = @json($codes);
        navigator.clipboard.writeText(codes.join("\n")).then(() => {
            const btn = event.target;
            btn.textContent = 'Codes copiés !';
            btn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');
            setTimeout(() => {
                btn.textContent = 'Copier tous les codes';
                btn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
            }, 2000);
        });
    }
    </script>
    @endif
</x-guest-layout>
