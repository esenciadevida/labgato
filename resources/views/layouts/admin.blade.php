<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - ' . config('app.name'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/LabGoTo_favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/LabGoTo_favicon_32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/LabGoTo_favicon_16.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/LabGoTo_favicon_192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/LabGoTo_favicon_512.png') }}">
    <x-brand.fonts />
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-4 focus:bg-slate-900 focus:text-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
        Skip to main content
    </a>

    <div x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" class="flex min-h-screen">
        <x-admin.sidebar />

        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 bg-slate-900/60 lg:hidden"
            aria-hidden="true"
        ></div>

        <div class="flex-1 flex flex-col min-w-0 transition-all duration-200">
            <x-admin.header />

            <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
                @yield('content')
            </main>

            <x-admin.footer />
        </div>
    </div>

    @stack('scripts')
    @livewireScriptConfig
    <x-ui.flash />
    <x-ui.confirm />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bulkForms = document.querySelectorAll('#bulk-form');

            bulkForms.forEach(function (form) {
                const selectAll = form.querySelector('#select-all');
                const checkboxes = form.querySelectorAll('.row-checkbox');
                const bulkActions = form.querySelector('#bulk-actions');
                const selectedCount = form.querySelector('#selected-count');
                const actionSelect = form.querySelector('#bulk-action-select');

                if (!selectAll || !bulkActions || !selectedCount || !actionSelect) {
                    return;
                }

                function updateBulkActions() {
                    const checked = form.querySelectorAll('.row-checkbox:checked');
                    selectedCount.textContent = checked.length;

                    if (checked.length > 0) {
                        bulkActions.classList.remove('hidden');
                        bulkActions.classList.add('flex');
                    } else {
                        bulkActions.classList.add('hidden');
                        bulkActions.classList.remove('flex');
                        actionSelect.value = '';
                    }
                }

                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (cb) {
                        cb.checked = selectAll.checked;
                    });
                    updateBulkActions();
                });

                checkboxes.forEach(function (cb) {
                    cb.addEventListener('change', function () {
                        selectAll.checked = Array.from(checkboxes).every(function (c) { return c.checked; });
                        updateBulkActions();
                    });
                });

                form.addEventListener('submit', function (e) {
                    if (!actionSelect.value) {
                        e.preventDefault();
                        alert('Please select a bulk action.');
                    }
                });
            });
        });
    </script>
</body>
</html>
