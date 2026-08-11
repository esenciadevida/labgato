@php
    $currentRoute = request()->route()->getName();
@endphp

<aside
    x-data
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
    @keydown.escape.window="sidebarOpen = false"
    role="navigation"
    aria-label="Admin navigation"
>
    <div class="flex items-center justify-between p-4 border-b border-gray-800">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-white">
            LabGato
        </a>
        <button
            @click="sidebarOpen = false"
            class="lg:hidden p-1 rounded text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
            aria-label="Close navigation"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto p-4 space-y-4">
        {{-- Dashboard --}}
        <ul class="space-y-1">
            <x-admin.navigation-item
                label="Dashboard"
                href="{{ route('admin.dashboard') }}"
                :active="$currentRoute === 'admin.dashboard'"
                permission="dashboard.view"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </x-admin.navigation-item>
        </ul>

        {{-- Content --}}
        @if(Auth::user()->hasAnyPermission(['tools.view', 'calculators.view', 'reference-ranges.view', 'sops.view', 'interpretation.view', 'knowledge.view']))
            <div>
                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Content</div>
                <ul class="mt-1 space-y-1">
                    <x-admin.navigation-item label="Tools" href="#" permission="tools.view" />
                    <x-admin.navigation-item
                        label="Calculators"
                        href="{{ route('admin.calculators.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.calculators')"
                        permission="calculators.view"
                    />
                    <x-admin.navigation-item label="Reference Ranges" href="#" permission="reference-ranges.view" />
                    <x-admin.navigation-item label="SOPs" href="#" permission="sops.view" />
                    <x-admin.navigation-item label="Interpretation" href="#" permission="interpretation.view" />
                    <x-admin.navigation-item label="Knowledge" href="#" permission="knowledge.view" />
                </ul>
            </div>
        @endif

        {{-- Taxonomy --}}
        @if(Auth::user()->hasAnyPermission(['taxonomy.view', 'taxonomy.manage']))
            <div>
                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Taxonomy</div>
                <ul class="mt-1 space-y-1">
                    <x-admin.navigation-item
                        label="Categories"
                        href="{{ route('admin.categories.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.categories')"
                        permission="taxonomy.view"
                    />
                    <x-admin.navigation-item
                        label="Tags"
                        href="{{ route('admin.tags.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.tags')"
                        permission="taxonomy.view"
                    />
                </ul>
            </div>
        @endif

        {{-- Users & Roles --}}
        @if(Auth::user()->hasAnyPermission(['users.view', 'roles.view']))
            <div>
                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Users & Roles</div>
                <ul class="mt-1 space-y-1">
                    <x-admin.navigation-item label="Users" href="#" permission="users.view" />
                    <x-admin.navigation-item label="Roles" href="#" permission="roles.view" />
                </ul>
            </div>
        @endif

        {{-- System --}}
        @if(Auth::user()->hasAnyPermission(['settings.view', 'audit-logs.view', 'analytics.view']))
            <div>
                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">System</div>
                <ul class="mt-1 space-y-1">
                    <x-admin.navigation-item label="Settings" href="#" permission="settings.view" />
                    <x-admin.navigation-item label="Audit Logs" href="#" permission="audit-logs.view" />
                    <x-admin.navigation-item label="Analytics" href="#" permission="analytics.view" />
                </ul>
            </div>
        @endif
    </nav>
</aside>
