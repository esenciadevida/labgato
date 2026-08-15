@extends('layouts.member')

@section('title', 'Recently Viewed - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">Recently Viewed</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Resources you have recently viewed.</p>
    </div>

    @if($recent->isEmpty())
        <x-ui.empty-state
            title="No recently viewed items"
            description="Items you view will appear here for quick access."
        />
    @else
        <div class="mb-4">
            <form method="POST" action="{{ route('recently-viewed.clear') }}" class="inline" data-confirm="Clear all recently viewed items?">
                @csrf
                @method('POST')
                <button type="submit" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 rounded">
                    Clear all history
                </button>
            </form>
        </div>

        <div class="space-y-3">
            @foreach($recent as $item)
                <a href="{{ $item->url ?? '#' }}" class="flex items-center justify-between p-4 rounded-lg border border-slate-200 bg-white dark:bg-slate-800 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $item->name ?? 'Unknown resource' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->resource_type ?? 'Resource' }} &#8226; Viewed {{ $item->viewed_at->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('recently-viewed.destroy', $item->id) }}" class="inline flex-shrink-0 ml-3" data-confirm="Remove this item from history?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded text-slate-400 hover:text-danger-600 dark:hover:text-danger-400 transition-colors" aria-label="Remove from history">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection