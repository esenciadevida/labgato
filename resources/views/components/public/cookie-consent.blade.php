<div
    x-data="{
        visible: false,
        init() {
            try {
                this.visible = ! localStorage.getItem('labgato-cookie-consent');
            } catch (e) {
                this.visible = true;
            }
        },
        choose(value) {
            try {
                localStorage.setItem('labgato-cookie-consent', value);
            } catch (e) {}
            this.visible = false;
            if (value === 'accepted') {
                @php $ga4Id = setting('analytics.ga4_measurement_id', ''); @endphp
                @if($ga4Id)
                try {
                    var s = document.createElement('script');
                    s.async = true;
                    s.src = 'https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}';
                    document.head.appendChild(s);
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '{{ $ga4Id }}');
                } catch(e) {}
                @endif
            }
        }
    }"
    x-show="visible"
    x-cloak
    x-transition.opacity.duration.300ms
    role="dialog"
    aria-label="Cookie consent"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-40 p-4 sm:p-6"
>
    <div class="pointer-events-auto mx-auto w-full max-w-3xl rounded-xl border border-slate-200 bg-white p-4 shadow-panel sm:p-5 dark:border-slate-700 dark:bg-slate-800">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-slate-600 dark:text-slate-300">
                <p>
                    We use cookies to improve your experience and analyze site traffic. By continuing, you agree to our use of cookies.
                    <a href="{{ route('legal.privacy-policy') }}" class="font-medium text-primary-700 underline hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Privacy Policy</a>
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button
                    type="button"
                    @click="choose('declined')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    Decline
                </button>
                <button
                    type="button"
                    @click="choose('accepted')"
                    class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                >
                    Accept
                </button>
            </div>
        </div>
    </div>
</div>
