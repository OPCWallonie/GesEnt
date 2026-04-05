@extends('errors.layout', [
    'code'    => 404,
    'color'   => 'blue',
    'title'   => 'Page introuvable',
    'message' => 'La page que vous cherchez n\'existe pas ou a été déplacée.',
    'icon'    => '<svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
])
