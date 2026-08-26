<footer class="bg-primary-950 text-white mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <div class="lg:col-span-5">
                <x-brand.logo dark size="xl" />
                <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/70">
                    Professional laboratory toolkit providing evidence-based calculators, reference ranges, and quality resources for laboratory professionals.
                </p>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-10 sm:gap-8">
                <div>
                    <h3 class="text-xs font-semibold uppercase text-white/60">Explore</h3>
                    <ul class="mt-5 space-y-2.5 text-sm">
                        <li><a href="{{ route('public.calculators.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">Calculators</a></li>
                        <li><a href="{{ route('public.reference-ranges.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">Reference Ranges</a></li>
                        <li><a href="{{ route('public.interpretation.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">Interpretations</a></li>
                        <li><a href="{{ route('public.tools.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">Tools</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase text-white/60">Resources</h3>
                    <ul class="mt-5 space-y-2.5 text-sm">
                        <li><a href="{{ route('public.sops.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">SOPs</a></li>
                        <li><a href="{{ route('public.knowledge.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">Knowledge Hub</a></li>
                        <li><a href="{{ route('public.search') }}" class="text-white/70 hover:text-lime-300 transition-colors">Search</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase text-white/60">Legal</h3>
                    <ul class="mt-5 space-y-2.5 text-sm">
                        <li><a href="{{ route('legal.privacy-policy') }}" class="text-white/70 hover:text-lime-300 transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.terms-of-service') }}" class="text-white/70 hover:text-lime-300 transition-colors">Terms of Service</a></li>
                        <li><a href="{{ route('legal.disclaimer') }}" class="text-white/70 hover:text-lime-300 transition-colors">Disclaimer</a></li>
                        <li><a href="{{ route('legal.accessibility-statement') }}" class="text-white/70 hover:text-lime-300 transition-colors">Accessibility Statement</a></li>
                        <li><a href="{{ route('legal.cookie-policy') }}" class="text-white/70 hover:text-lime-300 transition-colors">Cookie Policy</a></li>
                        <li><a href="{{ route('attributions.index') }}" class="text-white/70 hover:text-lime-300 transition-colors">Image Attributions</a></li>
                        <li><a href="{{ route('contact.create') }}" class="text-white/70 hover:text-lime-300 transition-colors">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-white/10 flex items-center justify-center gap-3 text-sm text-white/40">
            <span>&copy; {{ date('Y') }} Labgoto. All rights reserved.</span>
        </div>
    </div>
</footer>
