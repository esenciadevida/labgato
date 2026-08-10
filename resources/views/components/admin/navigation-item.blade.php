@props(['label' => null, 'href' => null, 'active' => false, 'permission' => null])

@if($permission === null || Auth::user()->hasPermission($permission))
    <li>
        @if($href)
            <a href="{{ $href }}"
               {{ $attributes->merge([
                   'class' => 'flex items-center gap-3 px-3 py-2 text-sm rounded-md transition-colors ' .
                       ($active
                           ? 'bg-gray-800 text-white font-medium'
                           : 'text-gray-400 hover:text-white hover:bg-gray-800/50')
               ]) }}
               @if($active) aria-current="page" @endif>
                @if($slot->isNotEmpty())
                    {{ $slot }}
                @endif
                <span>{{ $label }}</span>
            </a>
        @else
            <div {{ $attributes->merge(['class' => 'px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500']) }}>
                {{ $label }}
            </div>
        @endif
    </li>
@endif
