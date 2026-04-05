<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email *</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Mot de passe {{ isset($user) ? '(laisser vide pour ne pas changer)' : '*' }}
        </label>
        <input type="password" name="password" {{ isset($user) ? '' : 'required' }} minlength="8"
               class="w-full rounded-lg border-gray-300 shadow-sm text-sm"
               placeholder="{{ isset($user) ? '••••••••' : 'Minimum 8 caractères' }}">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation"
               class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
        <select name="role" required class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            @foreach($roles as $role)
                @php
                    $currentRole = isset($user) ? $user->roles->first()?->name : null;
                    $descriptions = [
                        'admin'     => 'Accès complet — gestion des utilisateurs, paramètres',
                        'comptable' => 'Accès aux documents financiers et exports',
                        'lecture'   => 'Consultation uniquement (lecture seule)',
                    ];
                @endphp
                <option value="{{ $role->name }}"
                        @selected(old('role', $currentRole) === $role->name)>
                    {{ ucfirst($role->name) }} — {{ $descriptions[$role->name] ?? '' }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
