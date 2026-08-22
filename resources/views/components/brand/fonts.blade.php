{{-- Self-hosted brand fonts (no external requests to fonts.googleapis.com / fonts.gstatic.com) --}}
<link rel="preload" href="{{ asset('assets/fonts/inter-latin-var.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('assets/fonts/space-grotesk-latin-var.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('assets/fonts/jetbrains-mono-latin-var.woff2') }}" as="font" type="font/woff2" crossorigin>

<style>
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 400 700;
        font-display: swap;
        src: url("{{ asset('assets/fonts/inter-latin-var.woff2') }}") format('woff2');
    }

    @font-face {
        font-family: 'Space Grotesk';
        font-style: normal;
        font-weight: 500 700;
        font-display: swap;
        src: url("{{ asset('assets/fonts/space-grotesk-latin-var.woff2') }}") format('woff2');
    }

    @font-face {
        font-family: 'JetBrains Mono';
        font-style: normal;
        font-weight: 400 600;
        font-display: swap;
        src: url("{{ asset('assets/fonts/jetbrains-mono-latin-var.woff2') }}") format('woff2');
    }
</style>
