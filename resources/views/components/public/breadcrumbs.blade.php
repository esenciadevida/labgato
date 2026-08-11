@props(['items' => []])

@if(count($items) > 0)
    <nav aria-label="Breadcrumb" class="py-3">
        <ol class="flex items-center flex-wrap gap-1 text-sm text-neutral-500">
            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if($index > 0)
                        <svg class="w-4 h-4 mx-1 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif
                    @if(isset($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-primary-700">{{ $item['label'] }}</a>
                    @else
                        <span class="text-neutral-900 font-medium" @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
