<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur {{ $code }} — Gesent</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center px-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-{{ $color ?? 'gray' }}-100 mb-6">
            {{ $icon }}
        </div>
        <h1 class="text-6xl font-bold text-gray-200 mb-2">{{ $code }}</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-3">{{ $title }}</h2>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">{{ $message }}</p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
               class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-100">
                ← Retour
            </a>
            <a href="/" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Tableau de bord
            </a>
        </div>
    </div>
</body>
</html>
