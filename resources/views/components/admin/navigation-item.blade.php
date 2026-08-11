@props(['label' => null, 'href' => null, 'active' => false, 'permission' => null])

@if($permission === null || Auth::user()->hasPermission($permission))
    <li>
        @if($href)
            <a href="{{ $href }}"
               {{ $attributes->merge([
                   'class' => 'flex items-center gap-3 px-3 py-2 text-sm rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 ' .
                       ($active
                           ? 'bg-primary-50 text-primary-700 font-medium'
                           : 'text-neutral-700 hover:bg-neutral-50')
               ]) }}
               @if($active) aria-current="page" @endif>
                @if($slot->isNotEmpty())
                    {{ $slot }}
                @endif
                <span>{{ $label }}</span>
            </a>
        @else
            <div {{ $attributes->merge(['class' => 'px-3 py-2 text-xs font-semibold uppercase tracking-wider text-neutral-500']) }}>
                {{ $label }}
            </div>
        @endif
    </li>
@endif
