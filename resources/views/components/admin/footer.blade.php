<footer class="bg-white border-t border-gray-200 px-4 sm:px-6 py-4">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
        <div class="flex items-center gap-2">
            <span class="font-medium text-gray-700">LabGato</span>
            <span>&middot;</span>
            <span>{{ app()->environment('local') ? 'Local' : 'Production' }}</span>
        </div>
        <div>
            &copy; {{ date('Y') }} LabGato. All rights reserved.
        </div>
    </div>
</footer>
