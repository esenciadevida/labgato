@php
    $currentRoute = request()->route()->getName();
@endphp

<header class="bg-white border-b border-neutral-200 sticky top-0 z-40" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="text-xl font-bold text-neutral-900">
                LabGato
            </a>

            <nav class="hidden md:flex items-center space-x-1" aria-label="Main navigation">
                <x-public.navigation href="{{ route('home') }}" :active="$currentRoute === 'home'">Home</x-public.navigation>
                <x-public.navigation href="{{ route('public.search') }}" :active="$currentRoute === 'public.search'">Search</x-public.navigation>
            </nav>

            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 rounded-md text-neutral-500 hover:text-neutral-700 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                :aria-expanded="mobileMenuOpen"
                aria-label="Toggle navigation menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <div
        x-show="mobileMenuOpen"
        @keydown.escape="mobileMenuOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="md:hidden border-t border-neutral-200"
        @click.outside="mobileMenuOpen = false"
    >
        <nav class="px-4 py-3 space-y-1" aria-label="Mobile navigation">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium text-neutral-700 hover:text-neutral-900 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 @if($currentRoute === 'home') bg-neutral-100 text-neutral-900 @endif">Home</a>
            <a href="{{ route('public.search') }}" class="block px-3 py-2 rounded-md text-base font-medium text-neutral-700 hover:text-neutral-900 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 @if($currentRoute === 'public.search') bg-neutral-100 text-neutral-900 @endif">Search</a>
        </nav>
    </div>
</header>
