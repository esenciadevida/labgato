@php
    $currentRoute = request()->route()->getName();
    $navItems = [
        ['label' => 'Calculators', 'route' => 'public.calculators.index', 'prefix' => 'public.calculators'],
        ['label' => 'Reference Ranges', 'route' => 'public.reference-ranges.index', 'prefix' => 'public.reference-ranges'],
        ['label' => 'Interpretations', 'route' => 'public.interpretation.index', 'prefix' => 'public.interpretation'],
        ['label' => 'Knowledge', 'route' => 'public.knowledge.index', 'prefix' => 'public.knowledge'],
        ['label' => 'SOPs', 'route' => 'public.sops.index', 'prefix' => 'public.sops'],
        ['label' => 'Tools', 'route' => 'public.tools.index', 'prefix' => 'public.tools'],
    ];
@endphp

<header id="site-header" class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm dark:bg-slate-900 dark:border-slate-800" x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between gap-4 h-20 lg:h-24">
            <div class="flex items-center shrink-0 mr-2 xl:mr-4">
                <x-brand.logo size="sm" theme imgClass="!h-6 sm:!h-8 lg:!h-10" />
            </div>

            <nav class="hidden xl:flex items-center gap-1 absolute left-1/2 -translate-x-1/2" aria-label="Main navigation">
                @foreach($navItems as $item)
                    @php $active = str_starts_with($currentRoute, $item['prefix']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="whitespace-nowrap px-2.5 xl:px-3 py-1.5 rounded text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $active ? 'text-primary-700 bg-primary-50 dark:text-primary-300 dark:bg-primary-900/30' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700' }}"
                        @if($active) aria-current="page" @endif
                    >{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <x-public.search.live-search />

                @auth
                    <span class="hidden lg:block w-px h-6 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                    <x-notifications.panel />
                    <x-public.user-dropdown />
                @else
                    <span class="hidden lg:block w-px h-6 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                    <x-theme-toggle />
                    <div class="hidden sm:flex items-center gap-2">
                        <x-ui.button href="{{ route('login') }}" variant="secondary" size="sm" class="h-10">
                            Sign in
                        </x-ui.button>
                        <x-ui.button href="{{ route('register') }}" variant="primary" size="sm" class="h-10">
                            Sign up
                        </x-ui.button>
                    </div>
                @endauth

                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="xl:hidden w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle navigation menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div
        x-cloak
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="xl:hidden border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
        @click.outside="mobileMenuOpen = false"
    >
        <div class="px-4 py-3 space-y-1">
            @foreach($navItems as $item)
                @php $active = str_starts_with($currentRoute, $item['prefix']); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @click="mobileMenuOpen = false"
                    class="block px-3 py-2.5 rounded text-sm font-medium min-h-[44px] {{ $active ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white' }}"
                >{{ $item['label'] }}</a>
            @endforeach
            <a
                href="{{ route('public.search') }}"
                @click="mobileMenuOpen = false"
                class="block px-3 py-2.5 rounded text-sm font-medium min-h-[44px] {{ $currentRoute === 'public.search' ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white' }}"
            >Search</a>

            <div class="border-t border-slate-200 pt-3 mt-1 dark:border-slate-700">
                @auth
                    @if(auth()->user()->isMember())
                        <a
                            href="{{ route('account.dashboard') }}"
                            @click="mobileMenuOpen = false"
                            class="block px-3 py-2.5 rounded text-sm font-medium min-h-[44px] text-slate-600 dark:text-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white"
                        >My Dashboard</a>
                    @endif
                    <a
                        href="{{ route('recently-viewed.index') }}"
                        @click="mobileMenuOpen = false"
                        class="block px-3 py-2.5 rounded text-sm font-medium min-h-[44px] text-slate-600 dark:text-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white"
                    >Recently Viewed</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded text-sm font-medium min-h-[44px] text-slate-600 dark:text-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">
                            Sign out
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        @click="mobileMenuOpen = false"
                        class="block px-3 py-2.5 rounded text-sm font-medium min-h-[44px] text-slate-600 dark:text-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white"
                    >Sign in</a>
                    <a
                        href="{{ route('register') }}"
                        @click="mobileMenuOpen = false"
                        class="mt-1 block px-3 py-2.5 rounded text-sm font-medium min-h-[44px] text-primary-700 bg-primary-50 dark:text-primary-300 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50"
                    >Sign up</a>
                @endauth
            </div>
        </div>
    </div>
</header>
