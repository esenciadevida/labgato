@php
    $currentRoute = request()->route()->getName();
    $items = [
        ['label' => 'My Dashboard', 'route' => 'account.dashboard'],
        ['label' => 'My Profile', 'route' => 'account.profile'],
        ['label' => 'Messages', 'route' => 'account.messages'],
    ];
@endphp

<nav data-tour="navigation" class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-4 dark:border-slate-800" aria-label="Member navigation">
    @foreach($items as $item)
        @php $active = $currentRoute === $item['route']; @endphp
        <a
            href="{{ route($item['route']) }}"
            class="inline-flex items-center rounded-lg px-3.5 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $active ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white' }}"
            @if($active) aria-current="page" @endif
        >{{ $item['label'] }}</a>
    @endforeach
</nav>
