@props(['label' => null, 'href' => null, 'active' => false, 'permission' => null, 'placeholder' => false, 'badge' => null])

@if($permission === null || Auth::user()->hasPermission($permission))
    <li>
        @if($href)
            <a href="{{ $href }}"
               {{ $attributes->merge([
                   'class' => 'relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white/20 ' .
                       ($active
                           ? 'bg-white/10 text-white font-medium'
                           : 'text-white/65 hover:text-white hover:bg-white/5')
               ]) }}
               :class="sidebarCollapsed && !sidebarOpen ? 'justify-center px-2' : ''"
               @if($active) aria-current="page" @endif
               :title="sidebarCollapsed && !sidebarOpen ? '{{ $label }}' : null">
                @if($active)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-r-full bg-primary-400" aria-hidden="true"></span>
                @endif
                <span class="w-5 h-5 shrink-0 {{ $active ? 'text-primary-300' : 'text-white/40' }}">{{ $slot }}</span>
                <span class="truncate" x-show="!sidebarCollapsed || sidebarOpen">{{ $label }}</span>
                @if($badge)
                    <span class="ml-auto shrink-0 rounded-full bg-danger-500 px-2 py-0.5 text-[10px] font-semibold text-white" x-show="!sidebarCollapsed || sidebarOpen">{{ $badge }}</span>
                @endif
            </a>
        @elseif($placeholder)
            <div {{ $attributes->merge(['class' => 'flex items-center justify-between gap-2 px-3 py-2.5 text-sm rounded-lg text-white/35 cursor-default select-none']) }}
                 :class="sidebarCollapsed && !sidebarOpen ? 'justify-center px-2' : ''"
                 aria-disabled="true"
                 :title="sidebarCollapsed && !sidebarOpen ? '{{ $label }} (Coming Soon)' : null">
                <span class="flex items-center gap-3 min-w-0" :class="sidebarCollapsed && !sidebarOpen ? 'justify-center' : ''">
                    <span class="w-5 h-5 shrink-0 text-white/25">{{ $slot }}</span>
                    <span class="truncate" x-show="!sidebarCollapsed || sidebarOpen">{{ $label }}</span>
                </span>
                <span class="shrink-0 rounded-full bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white/40" x-show="!sidebarCollapsed || sidebarOpen">Soon</span>
            </div>
        @else
            <div {{ $attributes->merge(['class' => 'px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white/40']) }} x-show="!sidebarCollapsed || sidebarOpen">
                {{ $label }}
            </div>
        @endif
    </li>
@endif
