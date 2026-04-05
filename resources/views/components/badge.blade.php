@props(['statut'])

@php
$colors = [
    'brouillon'   => 'bg-gray-100 text-gray-700',
    'en_attente'  => 'bg-yellow-100 text-yellow-800',
    'valide'      => 'bg-blue-100 text-blue-800',
    'en_cours'    => 'bg-indigo-100 text-indigo-800',
    'refuse'      => 'bg-red-100 text-red-800',
    'expire'      => 'bg-orange-100 text-orange-800',
    'envoyee'     => 'bg-cyan-100 text-cyan-800',
    'payee'       => 'bg-green-100 text-green-800',
    'en_retard'   => 'bg-red-100 text-red-800',
    'termine'     => 'bg-green-100 text-green-800',
    'archive'     => 'bg-gray-100 text-gray-500',
    'actif'       => 'bg-green-100 text-green-800',
    'inactif'     => 'bg-gray-100 text-gray-600',
];
$labels = [
    'brouillon'   => 'Brouillon',
    'en_attente'  => 'En attente',
    'valide'      => 'Validé',
    'en_cours'    => 'En cours',
    'refuse'      => 'Refusé',
    'expire'      => 'Expiré',
    'envoyee'     => 'Envoyée',
    'payee'       => 'Payée',
    'en_retard'   => 'En retard',
    'termine'     => 'Terminé',
    'archive'     => 'Archivé',
    'actif'       => 'Actif',
    'inactif'     => 'Inactif',
];
$color = $colors[$statut] ?? 'bg-gray-100 text-gray-700';
$label = $labels[$statut] ?? $statut;
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
    {{ $label }}
</span>
