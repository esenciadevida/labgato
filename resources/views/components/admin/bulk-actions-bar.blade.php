@props(['type' => null, 'route' => null])

<form
    method="POST"
    action="{{ $route }}"
    x-show="selected.length > 0"
    x-cloak
    x-ref="bulkForm"
    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800"
    role="toolbar"
    aria-label="Bulk actions for {{ $type }}"
>
    @csrf
    <input type="hidden" name="action" value="" x-ref="bulkAction">
    <input type="hidden" name="ids" :value="selected.join(',')">

    <span class="text-sm text-slate-600 dark:text-slate-300" x-text="selected.length + ' selected'"></span>

    <div class="flex items-center gap-2">
        <x-ui.button
            type="submit"
            variant="success"
            size="sm"
            @click.prevent="$refs.bulkAction.value = 'publish'; $refs.bulkForm.submit()"
        >
            Publish
        </x-ui.button>

        <x-ui.button
            type="submit"
            variant="secondary"
            size="sm"
            @click.prevent="$refs.bulkAction.value = 'archive'; $refs.bulkForm.submit()"
        >
            Archive
        </x-ui.button>

        <x-ui.button
            type="submit"
            variant="danger"
            size="sm"
            @click.prevent="if (confirm('Delete ' + selected.length + ' selected item(s)? This cannot be undone.')) { $refs.bulkAction.value = 'delete'; $refs.bulkForm.submit(); }"
        >
            Delete
        </x-ui.button>
    </div>
</form>
