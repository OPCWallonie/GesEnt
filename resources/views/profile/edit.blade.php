<x-app-layout>
    <x-slot name="header">Mon profil</x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- Informations personnelles --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Informations du compte</h2>
            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf @method('patch')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required autofocus
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email *</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Enregistrer
                    </button>
                    @if(session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                           class="text-sm text-green-600">Profil mis à jour.</p>
                    @endif
                </div>
            </form>
        </div>

        {{-- Mot de passe --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Changer le mot de passe</h2>
            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf @method('put')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel *</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('current_password', 'updatePassword')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe *</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    @error('password', 'updatePassword')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe *</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Changer le mot de passe
                    </button>
                    @if(session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                           class="text-sm text-green-600">Mot de passe mis à jour.</p>
                    @endif
                </div>
            </form>
        </div>

        {{-- Rôle & session --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Informations de session</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Rôle</dt>
                    <dd>
                        @foreach(auth()->user()->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' : ($role->name === 'comptable' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Membre depuis</dt>
                    <dd>{{ auth()->user()->created_at->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Authentification à deux facteurs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-4">Sécurité — Authentification à deux facteurs</h2>

            @if(auth()->user()->two_factor_enabled)
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <span class="text-sm text-green-700 font-medium">2FA activé</span>
                    <span class="text-xs text-gray-400">depuis {{ auth()->user()->two_factor_confirmed_at?->format('d/m/Y') }}</span>
                </div>
                <form method="POST" action="{{ route('2fa.disable') }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel (pour confirmer)</label>
                            <input type="password" name="password" required class="w-full rounded-lg border-gray-300 text-sm">
                            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                            Désactiver la 2FA
                        </button>
                    </div>
                </form>
            @else
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-3 h-3 bg-amber-400 rounded-full"></span>
                    <span class="text-sm text-amber-700 font-medium">2FA non activé</span>
                </div>
                <p class="text-sm text-gray-500 mb-4">
                    Protégez votre compte avec une application d'authentification
                    (Google Authenticator, Authy…).
                </p>
                <a href="{{ route('2fa.setup') }}"
                   class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Configurer la 2FA
                </a>
            @endif
        </div>

        {{-- Supprimer le compte --}}
        <div class="bg-red-50 border border-red-200 rounded-xl p-6" x-data="{ open: false }">
            <h2 class="text-base font-semibold text-red-800 mb-2">Zone dangereuse</h2>
            <p class="text-sm text-red-600 mb-4">La suppression de votre compte est irréversible. Toutes vos données seront perdues.</p>
            <button @click="open = true"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                Supprimer mon compte
            </button>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                <div @click.outside="open = false" class="bg-white rounded-xl shadow-xl p-6 w-96 space-y-4">
                    <h3 class="font-semibold text-gray-800">Confirmer la suppression</h3>
                    <p class="text-sm text-gray-600">Entrez votre mot de passe pour confirmer la suppression de votre compte.</p>
                    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-3">
                        @csrf @method('delete')
                        <input type="password" name="password" placeholder="Votre mot de passe" required
                               class="w-full rounded-lg border-gray-300 text-sm">
                        @error('password', 'userDeletion')
                            <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-3">
                            <button type="button" @click="open = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg">Annuler</button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Confirmer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
