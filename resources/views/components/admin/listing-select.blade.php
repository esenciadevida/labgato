@props(['ids' => []])

<div
    x-data="{
        selected: [],
        allIds: @js($ids),
        selectAll: false,
        toggle(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter((i) => i !== id);
            } else {
                this.selected.push(id);
            }
            this.selectAll = this.allIds.length > 0 && this.selected.length === this.allIds.length;
        },
        toggleAll() {
            this.selectAll = ! this.selectAll;
            this.selected = this.selectAll ? [...this.allIds] : [];
        },
        isSelected(id) {
            return this.selected.includes(id);
        }
    }"
    {{ $attributes }}
>
    {{ $slot }}
</div>
