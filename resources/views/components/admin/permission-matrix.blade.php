@props(['name' => 'permissions[]', 'grouped' => [], 'selected' => []])

@php
    $selected = collect($selected)->map(fn ($item) => (string) $item)->all();
@endphp

<div class="space-y-4">
    @foreach($grouped as $group => $permissions)
        <div class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 dark:bg-slate-800 dark:border-slate-700 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $group }}</span>
                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                    <input
                        type="checkbox"
                        class="permission-select-all rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"
                        data-group="{{ $group }}"
                        @checked(collect($permissions)->every(fn ($p) => in_array((string) $p->id, $selected)))
                    >
                    Select all
                </label>
            </div>
            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($permissions as $permission)
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input
                            type="checkbox"
                            name="{{ $name }}"
                            value="{{ $permission->id }}"
                            @checked(in_array((string) $permission->id, $selected))
                            class="permission-checkbox rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"
                            data-group="{{ $group }}"
                        >
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
