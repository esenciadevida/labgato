<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900">@yield('header', 'Dashboard')</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ auth()->user()->name ?? '' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Logout</button>
            </form>
        </div>
    </div>
</header>
