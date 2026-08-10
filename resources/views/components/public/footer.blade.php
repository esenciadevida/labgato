<footer class="bg-white border-t mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} LabGato. All rights reserved.</p>
            <div class="flex space-x-4 text-sm">
                <a href="{{ route('legal.privacy') }}" class="text-gray-500 hover:text-gray-700">Privacy</a>
                <a href="{{ route('legal.terms') }}" class="text-gray-500 hover:text-gray-700">Terms</a>
                <a href="{{ route('legal.disclaimer') }}" class="text-gray-500 hover:text-gray-700">Disclaimer</a>
                <a href="{{ route('legal.accessibility') }}" class="text-gray-500 hover:text-gray-700">Accessibility</a>
            </div>
        </div>
    </div>
</footer>
