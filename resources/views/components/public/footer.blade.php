<footer class="bg-white border-t mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-sm font-semibold text-neutral-900 uppercase tracking-wider">LabGato</h3>
                <p class="mt-2 text-sm text-neutral-500">
                    Professional laboratory toolkit providing essential resources for laboratory professionals.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-neutral-900 uppercase tracking-wider">Legal</h3>
                <ul class="mt-2 space-y-2 text-sm">
                    <li><a href="{{ route('legal.privacy-policy') }}" class="text-neutral-600 hover:text-primary-700">Privacy Policy</a></li>
                    <li><a href="{{ route('legal.terms-of-service') }}" class="text-neutral-600 hover:text-primary-700">Terms of Service</a></li>
                    <li><a href="{{ route('legal.disclaimer') }}" class="text-neutral-600 hover:text-primary-700">Disclaimer</a></li>
                    <li><a href="{{ route('legal.accessibility-statement') }}" class="text-neutral-600 hover:text-primary-700">Accessibility Statement</a></li>
                    <li><a href="{{ route('legal.cookie-policy') }}" class="text-neutral-600 hover:text-primary-700">Cookie Policy</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-neutral-900 uppercase tracking-wider">Admin</h3>
                <ul class="mt-2 space-y-2 text-sm">
                    <li><a href="{{ route('auth.login') }}" class="text-neutral-600 hover:text-primary-700">Admin Login</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 pt-8 border-t border-neutral-200 text-center text-sm text-neutral-500">
            &copy; {{ date('Y') }} LabGato. All rights reserved.
        </div>
    </div>
</footer>
