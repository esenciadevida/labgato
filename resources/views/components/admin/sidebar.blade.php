@php
    $currentRoute = request()->route()->getName();
    $pendingContributions = app(\App\Services\ContributionService::class)->pendingCount();
    $newContactMessages = \App\Models\ContactMessage::query()->where('status', 'new')->count();
@endphp

<aside
    x-data
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full lg:translate-x-0': !sidebarOpen,
        'lg:w-[4.5rem]': sidebarCollapsed,
        'lg:w-64': !sidebarCollapsed,
    }"
    class="fixed inset-y-0 left-0 z-40 bg-primary-950 border-r border-primary-800 flex flex-col transition-all duration-200 ease-in-out lg:static"
    :style="!sidebarOpen && !sidebarCollapsed ? 'transform: translateX(0)' : ''"
    @keydown.escape.window="sidebarOpen = false"
    role="navigation"
    aria-label="Admin navigation"
>
    <div class="flex items-center justify-between px-4 h-16 shrink-0 border-b border-white/10 bg-primary-950/90">
        <div class="flex items-center gap-3 min-w-0" x-show="!sidebarCollapsed || sidebarOpen">
            <x-brand.logo dark size="sm" href="{{ route('admin.dashboard') }}" />
            <span class="rounded-md bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/70">Admin</span>
        </div>
        <div class="flex items-center justify-center" x-show="sidebarCollapsed && !sidebarOpen">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/8 text-white ring-1 ring-white/10 shadow-md transition-colors hover:bg-white/12" aria-label="Admin Dashboard">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M9.5 3.5h5"/>
                    <path d="M10.75 3.5v5.2L5.4 18.4a1.6 1.6 0 0 0 1.4 2.4h10.4a1.6 1.6 0 0 0 1.4-2.4L13.25 8.7V3.5"/>
                    <path d="M6.9 16.2h10.2"/>
                </svg>
            </a>
        </div>
        <button
            @click="sidebarOpen = false"
            class="lg:hidden w-10 h-10 flex items-center justify-center rounded-full text-white/50 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30"
            aria-label="Close navigation"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <ul class="space-y-0.5">
            <x-admin.navigation-item
                label="Dashboard"
                href="{{ route('admin.dashboard') }}"
                :active="$currentRoute === 'admin.dashboard'"
                permission="dashboard.view"
                
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </x-admin.navigation-item>
        </ul>

        @if(Auth::user()->hasAnyPermission(['admin.full-access', 'questions.view', 'questions.import', 'categories.manage']))
            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/35" x-show="!sidebarCollapsed || sidebarOpen">Assessments</div>
                <ul class="space-y-0.5">
                    <x-admin.navigation-item
                        label="Launch Control"
                        href="{{ route('admin.launch') }}"
                        :active="str_starts_with($currentRoute, 'admin.launch')"
                        permission="admin.full-access"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Questions"
                        href="{{ route('admin.questions.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.questions') && ! str_contains($currentRoute, 'import') && ! str_contains($currentRoute, 'categories')"
                        permission="questions.view"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Import Questions"
                        href="{{ route('admin.questions.import') }}"
                        :active="str_starts_with($currentRoute, 'admin.questions.import')"
                        permission="questions.import"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Subjects"
                        href="{{ route('admin.question-categories.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.question-categories')"
                        permission="categories.manage"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                        </svg>
                    </x-admin.navigation-item>
                </ul>
            </div>
        @endif

        @if(Auth::user()->hasAnyPermission(['tools.view', 'calculators.view', 'reference-ranges.view', 'sops.view', 'interpretation.view', 'knowledge.view', 'contributions.review']))
            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/35" x-show="!sidebarCollapsed || sidebarOpen">Content</div>
                <ul class="space-y-0.5">
                    <x-admin.navigation-item
                        label="Tools"
                        href="{{ route('admin.tools.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.tools')"
                        permission="tools.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Calculators"
                        href="{{ route('admin.calculators.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.calculators')"
                        permission="calculators.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Reference Ranges"
                        href="{{ route('admin.reference-ranges.index') }}"
                        :active="in_array($currentRoute, ['admin.reference-ranges.index', 'admin.reference-ranges.create', 'admin.reference-ranges.edit'])"
                        permission="reference-ranges.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Import CSV"
                        href="{{ route('admin.reference-ranges.import') }}"
                        :active="str_starts_with($currentRoute, 'admin.reference-ranges.import')"
                        permission="reference-ranges.import"
                        :visible="Auth::user()->hasPermission('reference-ranges.import')"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l-4-4"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="SOPs"
                        href="{{ route('admin.sops.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.sops')"
                        permission="sops.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Interpretation"
                        href="{{ route('admin.interpretation.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.interpretation')"
                        permission="interpretation.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Knowledge"
                        href="{{ route('admin.knowledge.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.knowledge')"
                        permission="knowledge.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Contributions"
                        href="{{ route('admin.contributions.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.contributions')"
                        permission="contributions.review"
                        :badge="$pendingContributions > 0 ? $pendingContributions : null"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                        </svg>
                    </x-admin.navigation-item>
                </ul>
            </div>
        @endif

        @if(Auth::user()->hasAnyPermission(['taxonomy.view', 'taxonomy.manage']))
            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/35" x-show="!sidebarCollapsed || sidebarOpen">Taxonomy</div>
                <ul class="space-y-0.5">
                    <x-admin.navigation-item
                        label="Categories"
                        href="{{ route('admin.categories.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.categories')"
                        permission="taxonomy.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Tags"
                        href="{{ route('admin.tags.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.tags')"
                        permission="taxonomy.view"
                        
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5"/>
                        </svg>
                    </x-admin.navigation-item>
                </ul>
            </div>
        @endif

        @if(Auth::user()->hasAnyPermission(['users.view', 'roles.view']))
            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/35" x-show="!sidebarCollapsed || sidebarOpen">People</div>
                <ul class="space-y-0.5">
                    @if(Auth::user()->isSuperAdministrator())
                        <x-admin.navigation-item
                            label="Administrators"
                            href="{{ route('admin.administrators.index') }}"
                            :active="str_starts_with($currentRoute, 'admin.administrators')"
                            permission="users.view"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                            </svg>
                        </x-admin.navigation-item>
                    @endif
                    <x-admin.navigation-item
                        label="Users"
                        href="{{ route('admin.users.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.users')"
                        permission="users.view"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Roles"
                        href="{{ route('admin.roles.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.roles')"
                        permission="roles.view"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                    </x-admin.navigation-item>
                </ul>
            </div>
        @endif

        @if(Auth::user()->hasAnyPermission(['settings.view', 'audit-logs.view', 'contact-messages.view']))
            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/35" x-show="!sidebarCollapsed || sidebarOpen">System</div>
                <ul class="space-y-0.5">
                    <x-admin.navigation-item
                        label="Contact Messages"
                        href="{{ route('admin.contact-messages.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.contact-messages')"
                        permission="contact-messages.view"
                        :badge="$newContactMessages > 0 ? $newContactMessages : null"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Settings"
                        href="{{ route('admin.settings.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.settings')"
                        permission="settings.view"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Audit Logs"
                        href="{{ route('admin.audit-logs.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.audit-logs')"
                        permission="audit-logs.view"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                        </svg>
                    </x-admin.navigation-item>
                </ul>
            </div>
        @endif
        @if(Auth::user()->hasAnyPermission(['popups.manage', 'announcements.manage']))
            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/35" x-show="!sidebarCollapsed || sidebarOpen">Communication</div>
                <ul class="space-y-0.5">
                    <x-admin.navigation-item
                        label="Pop-ups"
                        href="{{ route('admin.popups.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.popups')"
                        permission="popups.manage"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/>
                        </svg>
                    </x-admin.navigation-item>
                    <x-admin.navigation-item
                        label="Announcements"
                        href="{{ route('admin.announcements.index') }}"
                        :active="str_starts_with($currentRoute, 'admin.announcements')"
                        permission="announcements.manage"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/>
                        </svg>
                    </x-admin.navigation-item>
                </ul>
            </div>
        @endif
    </nav>

    <div class="shrink-0 border-t border-white/10 p-3" x-show="!sidebarCollapsed || sidebarOpen">
        <div class="flex items-center gap-3 rounded-lg px-2 py-1.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-white/40">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full text-white/40 hover:text-white hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 transition-colors" aria-label="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    <div class="shrink-0 border-t border-white/10 p-2 flex justify-center" x-show="sidebarCollapsed && !sidebarOpen">
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full text-white/40 hover:text-white hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 transition-colors" aria-label="Logout">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</aside>
