@props(['url' => null])

@php
    $shareUrl = $url ?? url()->current();
    $encodedUrl = addslashes($shareUrl);
@endphp

<div
    x-data="{
        url: '{{ $encodedUrl }}',
        copied: false,
        share() {
            const self = this;
            const fallback = () => {
                const textarea = document.createElement('textarea');
                textarea.value = this.url;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                textarea.style.top = '-9999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try { document.execCommand('copy'); self.showCopied(); } catch (e) {}
                document.body.removeChild(textarea);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(this.url)
                    .then(() => self.showCopied())
                    .catch(fallback);
            } else {
                fallback();
            }
        },
        showCopied() {
            this.copied = true;
            clearTimeout(this._timer);
            this._timer = setTimeout(() => { this.copied = false; }, 2000);
        }
    }"
    class="inline-flex"
>
    <button
        type="button"
        @click="share()"
        :aria-label="copied ? 'Link copied!' : 'Share this page'"
        class="inline-flex items-center gap-2 rounded-lg bg-white/10 border border-white/20 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 focus:ring-offset-primary-950 transition-colors"
    >
        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.538 9.606 12 10.437 12H17a4 4 0 100-8h-1.992a2 2 0 00-1.44.614l-.644.672a2 2 0 01-1.44.614H9.43c-.83 0-1.55.538-1.752 1.342l-2.287 8.128A1 1 0 006.358 18h1.43a2 2 0 001.94-1.515l.463-1.843z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 16h-6a4 4 0 00-4 4" />
        </svg>
        <svg x-show="copied" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span x-show="!copied">Share</span>
        <span x-show="copied" x-cloak>Link copied!</span>
    </button>
    <span x-show="copied" x-cloak aria-live="polite" class="sr-only">Link copied!</span>
</div>
