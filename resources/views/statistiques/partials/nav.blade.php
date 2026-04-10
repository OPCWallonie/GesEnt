<div class="flex flex-wrap gap-1 bg-white rounded-xl shadow-sm border border-gray-200 p-1 mb-6">
    <a href="{{ route('statistiques.index') }}{{ request()->has('annee') ? '?annee='.request('annee') : '' }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('statistiques.index') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        Vue d'ensemble
    </a>
    <a href="{{ route('statistiques.balance-agee') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('statistiques.balance-agee') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        Balance âgée
    </a>
    <a href="{{ route('statistiques.tresorerie') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('statistiques.tresorerie') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        Trésorerie
    </a>
    <a href="{{ route('statistiques.chantiers') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('statistiques.chantiers') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        Chantiers
    </a>
</div>
