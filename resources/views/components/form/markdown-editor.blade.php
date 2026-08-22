@props([
    'name' => 'content',
    'rows' => 16,
    'type' => null,
    'error' => null,
])

@php
    $editorId = $attributes->get('id') ?? $name;
@endphp

<div {{ $attributes }}
    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop="onDrop($event)"
    data-upload-url="{{ route('content-images.store') }}"
    data-preview-url="{{ route('content.preview') }}"
    data-csrf="{{ csrf_token() }}"
    data-type="{{ $type }}"
    x-data="{
        uploading: false,
        uploadError: '',
        previewing: false,
        previewHtml: '',
        dragging: false,

        changed() {
            this.$refs.ta.dispatchEvent(new Event('input', { bubbles: true }))
        },

        insert(text) {
            const ta = this.$refs.ta
            const start = ta.selectionStart ?? ta.value.length
            const end = ta.selectionEnd ?? start
            ta.setRangeText(text, start, end, 'end')
            this.changed()
            ta.focus()
        },

        wrap(before, after) {
            const ta = this.$refs.ta
            const selected = ta.value.slice(ta.selectionStart, ta.selectionEnd)
            if (!selected) return
            ta.setRangeText(before + selected + after, ta.selectionStart, ta.selectionEnd, 'end')
            this.changed()
            ta.focus()
        },

        lineBounds() {
            const ta = this.$refs.ta
            let start = ta.selectionStart
            while (start > 0 && ta.value[start - 1] !== '\n') start--
            let end = ta.selectionEnd
            while (end < ta.value.length && ta.value[end] !== '\n') end++
            return [start, end]
        },

        heading(level) {
            const ta = this.$refs.ta
            const [ls, le] = this.lineBounds()
            const lines = ta.value.slice(ls, le).split('\n').map(line => {
                const stripped = line.replace(/^#{1,6}\s+/, '')
                return '#'.repeat(level) + ' ' + stripped
            })
            ta.setRangeText(lines.join('\n'), ls, le, 'end')
            this.changed()
            ta.focus()
        },

        list(marker) {
            const ta = this.$refs.ta
            const [ls, le] = this.lineBounds()
            const lines = ta.value.slice(ls, le).split('\n').map((line, i) =>
                marker === 'ol' ? (i + 1) + '. ' + line.replace(/^(\d+\.\s+|[-*+]\s+)/, '') : '- ' + line.replace(/^(\d+\.\s+|[-*+]\s+)/, '')
            )
            ta.setRangeText(lines.join('\n'), ls, le, 'end')
            this.changed()
            ta.focus()
        },

        link() {
            const ta = this.$refs.ta
            const selected = ta.value.slice(ta.selectionStart, ta.selectionEnd) || 'link text'
            this.insert('[' + selected + '](https://)')
        },

        code() {
            this.wrap('`', '`')
        },

        pickImage() {
            if (this.uploading) return
            this.$refs.file.click()
        },

        async upload(file) {
            if (!file || !file.type.startsWith('image/')) {
                this.uploadError = 'Please choose an image file.'
                return
            }
            if (file.size > 5 * 1024 * 1024) {
                this.uploadError = 'Image exceeds the 5 MB limit.'
                return
            }

            this.uploading = true
            this.uploadError = ''

            try {
                const body = new FormData()
                body.append('image', file)
                body.append('type', this.$root.dataset.type || '')

                const res = await fetch(this.$root.dataset.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.$root.dataset.csrf, 'Accept': 'application/json' },
                    body,
                })
                const data = await res.json().catch(() => ({}))

                if (!res.ok) throw new Error(data.message || 'Upload failed.')

                const alt = data.alt || (file.name || 'image')
                    .replace(/\.[a-z0-9]+$/i, '')
                    .replace(/[_-]+/g, ' ')
                    .trim()
                    .slice(0, 80) || 'image'

                this.insert('\n![' + alt + '](' + data.url + ')\n')
            } catch (e) {
                this.uploadError = e.message || 'Upload failed.'
            } finally {
                this.uploading = false
                this.$refs.file.value = ''
            }
        },

        onPaste(e) {
            const files = Array.from(e.clipboardData?.files || []).filter(f => f.type.startsWith('image/'))
            if (files.length) {
                e.preventDefault()
                files.forEach(f => this.upload(f))
            }
        },

        onDrop(e) {
            this.dragging = false
            const files = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'))
            if (files.length) {
                e.preventDefault()
                files.forEach(f => this.upload(f))
            }
        },

        onKeydown(e) {
            if (!(e.ctrlKey || e.metaKey)) return
            const key = e.key.toLowerCase()
            if (key === 'b') { e.preventDefault(); this.wrap('**', '**') }
            else if (key === 'i') { e.preventDefault(); this.wrap('*', '*') }
            else if (key === 'k') { e.preventDefault(); this.link() }
        },

        async togglePreview() {
            if (this.previewing) {
                this.previewing = false
                return
            }
            const content = this.$refs.ta.value.trim()
            if (!content) return

            try {
                const res = await fetch(this.$root.dataset.previewUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.$root.dataset.csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ content }),
                })
                const data = await res.json().catch(() => ({}))
                if (!res.ok) throw new Error(data.message || 'Preview failed.')
                this.previewHtml = data.html
                this.previewing = true
            } catch (e) {
                this.uploadError = e.message
            }
        },
    }"
    class="markdown-editor"
>
    <div class="flex flex-wrap items-center gap-1 rounded-t-lg border border-b-0 border-slate-300 bg-slate-50 px-2 py-1.5 dark:border-slate-700 dark:bg-slate-800/60">
        <button type="button" x-on:click="heading(2)" title="Heading" aria-label="Heading"
            class="rounded px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">H2</button>
        <button type="button" x-on:click="heading(3)" title="Subheading" aria-label="Subheading"
            class="rounded px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">H3</button>
        <span class="mx-1 h-4 w-px bg-slate-300 dark:bg-slate-600"></span>
        <button type="button" x-on:click="wrap('**', '**')" title="Bold (Ctrl+B)" aria-label="Bold"
            class="rounded px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">B</button>
        <button type="button" x-on:click="wrap('*', '*')" title="Italic (Ctrl+I)" aria-label="Italic"
            class="rounded px-2 py-1 font-serif text-xs italic text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">I</button>
        <button type="button" x-on:click="code()" title="Inline code" aria-label="Inline code"
            class="rounded px-2 py-1 font-mono text-xs text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">&lt;/&gt;</button>
        <span class="mx-1 h-4 w-px bg-slate-300 dark:bg-slate-600"></span>
        <button type="button" x-on:click="list('-')" title="Bullet list" aria-label="Bullet list"
            class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">&bull; List</button>
        <button type="button" x-on:click="list('ol')" title="Numbered list" aria-label="Numbered list"
            class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">1. List</button>
        <button type="button" x-on:click="link()" title="Link (Ctrl+K)" aria-label="Insert link"
            class="rounded px-2 py-1 text-xs text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">Link</button>
        <span class="mx-1 h-4 w-px bg-slate-300 dark:bg-slate-600"></span>
        <button type="button" x-on:click="pickImage()" x-bind:disabled="uploading" title="Insert image (upload, paste, or drag & drop)" aria-label="Insert image"
            class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-primary-700 hover:bg-primary-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-primary-400 dark:hover:bg-primary-500/10">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25v9.5A2.25 2.25 0 0116.75 17H3.25A2.25 2.25 0 011 14.75v-9.5zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75v-2.69l-2.22-2.219a.75.75 0 00-1.06 0l-1.91 1.909.47.47a.75.75 0 01-1.06 1.06L6.53 8.091l-4.03 4.03v-1.06l3.5-3.5a.75.75 0 011.06 0l1.47 1.47 1.97-1.97a2.25 2.25 0 013.18 0L16.5 9.94l-1.28-1.28a.75.75 0 00-1.06 0z" clip-rule="evenodd" />
            </svg>
            <span x-text="uploading ? 'Uploading…' : 'Image'"></span>
        </button>
        <button type="button" x-on:click="togglePreview()" title="Toggle preview" aria-label="Toggle preview"
            class="ml-auto rounded px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
            x-text="previewing ? 'Edit' : 'Preview'"></button>
    </div>

    <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden" x-ref="file"
        x-on:change="if ($event.target.files.length) upload($event.target.files[0])">

    <div class="relative">
        <textarea
            x-ref="ta"
            id="{{ $editorId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            x-show="!previewing"
            x-on:paste="onPaste($event)"
            x-on:keydown="onKeydown($event)"
            {{ $attributes->only(['required', 'aria-required', 'placeholder']) }}
            class="block w-full rounded-b-lg border border-slate-300 bg-white px-3.5 py-2.5 font-mono text-sm leading-relaxed text-slate-900 placeholder-slate-400 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500"
        >{{ $slot }}</textarea>

        <div x-show="previewing" x-cloak class="rich-content min-h-[{{ $rows }}rem] rounded-b-lg border border-slate-300 bg-white px-4 py-3 text-sm shadow-card dark:border-slate-700 dark:bg-slate-800" x-html="previewHtml"></div>

        <div x-show="dragging" x-cloak
            class="pointer-events-none absolute inset-0 flex items-center justify-center rounded-lg border-2 border-dashed border-primary-500 bg-primary-50/80 text-sm font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
            Drop image to insert
        </div>
    </div>

    @if ($error)
        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $error }}</p>
    @endif
    <p x-show="uploadError" x-cloak x-text="uploadError" class="mt-1 text-xs text-danger-600 dark:text-danger-400"></p>
</div>
