@php
    $announcements = app(\App\Services\PublicNoticeService::class)->activeAnnouncements()
        ->filter(fn ($announcement) => trim($announcement->message) !== '');

    $typeClasses = [
        'info' => 'bg-blue-600',
        'success' => 'bg-green-600',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-600',
    ];
@endphp

@if($announcements->isNotEmpty())
    <div class="relative z-40">
        @foreach($announcements as $announcement)
            @php
                $bannerClass = $typeClasses[$announcement->type] ?? $typeClasses['info'];
            @endphp
            <div
                x-data="announcementBanner(@js($announcement->id))"
                x-show="!dismissed"
                x-cloak
                data-announcement-id="{{ $announcement->id }}"
                data-type="{{ $announcement->type }}"
                data-dismiss-key="labgato-dismissed-announcement-{{ $announcement->id }}"
                class="{{ $bannerClass }} text-white"
                role="status"
            >
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        <p class="truncate text-sm font-medium">
                            {{ $announcement->message }}
                            @if($announcement->link_text && $announcement->link_url)
                                <a href="{{ $announcement->link_url }}" class="ml-1 inline-flex items-center gap-1 underline underline-offset-2 hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 rounded-sm">
                                    {{ $announcement->link_text }}
                                </a>
                            @endif
                        </p>
                    </div>
                    @if($announcement->is_dismissible)
                        <button
                            type="button"
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white/80 transition-colors hover:bg-white/20 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                            x-on:click="dismiss()"
                            aria-label="Dismiss announcement"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
