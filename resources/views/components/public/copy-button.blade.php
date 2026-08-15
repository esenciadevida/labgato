@props(['value' => '', 'label' => 'Copy to clipboard'])

<div x-data="{
    copied: false,
    copy() {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText('{{ addslashes($value) }}').then(() => this.showCopied());
        } else {
            const ta = document.createElement('textarea');
            ta.value = '{{ addslashes($value) }}';
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            ta.style.top = '-9999px';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { document.execCommand('copy'); this.showCopied(); } catch (e) {}
            document.body.removeChild(ta);
        }
    },
    showCopied() {
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 2000);
    }
}" class="inline-flex">
    <button
        type="button"
        @click="copy()"
        :aria-label="copied ? 'Copied' : '{{ addslashes($label) }}'"
        class="no-print inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
    >
        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/>
        </svg>
        <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/>
        </svg>
        <span x-show="!copied">Copy</span>
        <span x-show="copied" x-cloak>Copied!</span>
    </button>
    <span x-show="copied" x-cloak aria-live="polite" class="sr-only">Copied to clipboard</span>
</div>
