@extends('layouts.admin')

@section('title', 'Certificate Template - ' . config('app.name'))

@section('header', 'Certificate Template')

@section('content')
@can('certificates.manage')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.certificates.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to certificates
        </a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="space-y-6">
                <x-ui.card title="Signatory">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-form.label for="tpl_signatory_name">Signatory Name</x-form.label>
                            <x-ui.input
                                id="tpl_signatory_name"
                                name="settings[certificate.signatory_name]"
                                value="{{ old('settings.certificate.signatory_name', setting('certificate.signatory_name', 'Program Director')) }}"
                            />
                        </div>
                        <div>
                            <x-form.label for="tpl_signatory_title">Signatory Title</x-form.label>
                            <x-ui.input
                                id="tpl_signatory_title"
                                name="settings[certificate.signatory_title]"
                                value="{{ old('settings.certificate.signatory_title', setting('certificate.signatory_title', 'Program Director, LabGOTO')) }}"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <x-form.label for="tpl_signature">Signature Image</x-form.label>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Transparent-background PNG/JPG. Placed above the signatory name.</p>
                            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-start">
                                <div class="w-full sm:w-64">
                                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Current</p>
                                    <div class="flex h-16 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white px-4 dark:border-slate-600 dark:bg-slate-800">
                                        @if($hasSignature)
                                            <img src="{{ $signatureUrl }}" alt="Signature" class="h-10 w-44 object-contain">
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-slate-500">No signature uploaded</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1 space-y-3">
                                    <x-form.label for="tpl_signature">Upload new</x-form.label>
                                    <x-ui.input type="file" id="tpl_signature" name="certificate_signature" accept="image/png,image/jpeg" />
                                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <input type="checkbox" name="certificate_signature_remove" value="1" class="rounded border-slate-300 text-danger-600 focus:ring-danger-500 dark:border-slate-600 dark:bg-slate-800">
                                        Remove current
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Template Colors">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-form.label for="tpl_primary_color">Primary Color</x-form.label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" id="tpl_primary_color_picker" name="settings[certificate.primary_color]"
                                    value="{{ old('settings.certificate.primary_color', setting('certificate.primary_color', '#1e3a8a')) }}"
                                    class="h-9 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                                <x-ui.input
                                    id="tpl_primary_color"
                                    name="settings[certificate.primary_color]"
                                    value="{{ old('settings.certificate.primary_color', setting('certificate.primary_color', '#1e3a8a')) }}"
                                    class="flex-1 font-mono text-xs"
                                />
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Border, holder name, headings.</p>
                        </div>
                        <div>
                            <x-form.label for="tpl_secondary_color">Secondary Color</x-form.label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" id="tpl_secondary_color_picker" name="settings[certificate.secondary_color]"
                                    value="{{ old('settings.certificate.secondary_color', setting('certificate.secondary_color', '#d4af37')) }}"
                                    class="h-9 w-14 cursor-pointer rounded border border-slate-300 dark:border-slate-600">
                                <x-ui.input
                                    id="tpl_secondary_color"
                                    name="settings[certificate.secondary_color]"
                                    value="{{ old('settings.certificate.secondary_color', setting('certificate.secondary_color', '#d4af37')) }}"
                                    class="flex-1 font-mono text-xs"
                                />
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Inner frame, rules, ribbon accent.</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Template Text">
                    <div class="space-y-5">
                        <div>
                            <x-form.label for="tpl_body_text">Body Text</x-form.label>
                            <textarea
                                id="tpl_body_text"
                                name="settings[certificate.body_text]"
                                rows="3"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary-500"
                                placeholder="Leave blank for default"
                            >{{ old('settings.certificate.body_text', setting('certificate.body_text', '')) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Use system variables for dynamic content.</p>
                        </div>
                        <div>
                            <x-form.label for="tpl_disclaimer_text">Disclaimer Text</x-form.label>
                            <textarea
                                id="tpl_disclaimer_text"
                                name="settings[certificate.disclaimer_text]"
                                rows="3"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary-500"
                                placeholder="Leave blank for default"
                            >{{ old('settings.certificate.disclaimer_text', setting('certificate.disclaimer_text', '')) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Shown at the bottom of every certificate.</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Options">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-form.label>Show Logo</x-form.label>
                            <input type="hidden" name="settings[certificate.show_logo]" value="false">
                            <label class="inline-flex cursor-pointer select-none items-center gap-2">
                                <input type="checkbox" name="settings[certificate.show_logo]" value="true"
                                    id="tpl_show_logo"
                                    class="h-4 w-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800"
                                    @checked(setting_bool('certificate.show_logo', true))>
                                <span class="text-sm text-slate-700 dark:text-slate-300">Enabled</span>
                            </label>
                        </div>
                        <div>
                            <x-form.label>Show QR Code</x-form.label>
                            <input type="hidden" name="settings[certificate.show_qr]" value="false">
                            <label class="inline-flex cursor-pointer select-none items-center gap-2">
                                <input type="checkbox" name="settings[certificate.show_qr]" value="true"
                                    id="tpl_show_qr"
                                    class="h-4 w-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800"
                                    @checked(setting_bool('certificate.show_qr', true))>
                                <span class="text-sm text-slate-700 dark:text-slate-300">Enabled</span>
                            </label>
                        </div>
                    </div>
                </x-ui.card>

                <div class="flex items-center justify-end gap-3">
                    <x-ui.button type="submit">Save Template</x-ui.button>
                </div>
            </div>

            <div class="xl:sticky xl:top-24 xl:self-start">
                <x-ui.card title="Live Preview" class="!p-0 overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-4 py-2 dark:border-slate-800 dark:bg-slate-800/60">
                        <button type="button" id="refresh-preview"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh Preview
                        </button>
                    </div>
                    <div class="p-4">
                        <div id="preview-container" class="mx-auto" style="max-width: 700px;">
                            <div id="preview-certificate" class="relative overflow-hidden rounded-lg border-2 bg-white" style="aspect-ratio: 280/184;">
                                <div id="preview-frame" class="absolute inset-1 border pointer-events-none"></div>
                                <div class="relative flex h-full flex-col items-center justify-center p-6 text-center" id="preview-content">
                                    <div id="preview-logo" class="mb-3">
                                        <div class="text-xl font-extrabold tracking-wider" id="preview-primary-color-el">LabGOTO</div>
                                        <div class="text-[8px] font-semibold uppercase tracking-widest text-slate-400">Medical Laboratory Knowledge Platform</div>
                                    </div>
                                    <div id="preview-type-ribbon" class="mb-3 inline-block rounded-full border px-4 py-1 text-[10px] font-extrabold uppercase tracking-[3px]" style="color: #6b5320;">Achievement</div>
                                    <p class="text-[11px] italic text-slate-400">This certificate is proudly presented to</p>
                                    <p id="preview-holder" class="mt-1 font-serif text-2xl font-bold" style="color: #1e3a8a;">Maria Santos</p>
                                    <div id="preview-holder-rule" class="mt-1 h-px w-2/3" style="background: #d4af37;"></div>
                                    <p id="preview-body" class="mt-2 text-xs text-slate-600">for successfully completing the <strong>Hematology Proficiency Exam</strong></p>
                                    <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between border-t pt-2" id="preview-footer-border">
                                        <div class="text-[9px] text-slate-400">
                                            <div class="font-serif font-bold text-slate-600" id="preview-signatory">Program Director</div>
                                            <div class="uppercase tracking-wider">Program Director, LabGOTO</div>
                                        </div>
                                        <div id="preview-qr" class="text-center">
                                            <div class="mx-auto mb-0.5 flex h-10 w-10 items-center justify-center rounded border border-dashed border-slate-300 text-[7px] text-slate-400">QR</div>
                                            <div class="text-[7px] text-slate-400">Scan to verify</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="preview-disclaimer" class="mt-2 text-[9px] leading-relaxed text-slate-500 text-justify">
                                This certificate acknowledges achievement in the Hematology Proficiency Exam administered by LabGOTO.
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <div class="mt-4">
                    <x-ui.card title="System Variables">
                        <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">Use these placeholders in body and disclaimer text:</p>
                        <div class="space-y-1.5">
                            @foreach($variables as $var => $desc)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $var }}</code>
                                    <span class="text-slate-500 dark:text-slate-400">{{ $desc }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewEl = document.getElementById('preview-certificate');
    const frameEl = document.getElementById('preview-frame');
    const primaryColorEl = document.getElementById('preview-primary-color-el');
    const holderEl = document.getElementById('preview-holder');
    const holderRule = document.getElementById('preview-holder-rule');
    const bodyEl = document.getElementById('preview-body');
    const disclaimerEl = document.getElementById('preview-disclaimer');
    const qrEl = document.getElementById('preview-qr');
    const logoEl = document.getElementById('preview-logo');
    const footerBorder = document.getElementById('preview-footer-border');

    const primaryPicker = document.getElementById('tpl_primary_color_picker');
    const primaryInput = document.getElementById('tpl_primary_color');
    const secondaryPicker = document.getElementById('tpl_secondary_color_picker');
    const secondaryInput = document.getElementById('tpl_secondary_color');
    const bodyInput = document.getElementById('tpl_body_text');
    const disclaimerInput = document.getElementById('tpl_disclaimer_text');
    const showLogo = document.getElementById('tpl_show_logo');
    const showQr = document.getElementById('tpl_show_qr');
    const signatoryInput = document.getElementById('tpl_signatory_name');
    const refreshBtn = document.getElementById('refresh-preview');

    const sample = @json($sampleVariables);

    function getColors() {
        return { primary: primaryInput.value || '#1e3a8a', secondary: secondaryInput.value || '#d4af37' };
    }

    function applyPreview() {
        const c = getColors();

        previewEl.style.borderColor = c.primary;
        frameEl.style.borderColor = c.secondary;
        primaryColorEl.style.color = c.primary;
        holderEl.style.color = c.primary;
        holderRule.style.background = c.secondary;

        const typeRibbon = document.getElementById('preview-type-ribbon');
        typeRibbon.style.borderColor = c.secondary;

        footerBorder.style.borderColor = c.secondary;

        logoEl.style.display = showLogo.checked ? '' : 'none';

        if (showQr.checked) {
            qrEl.style.display = '';
        } else {
            qrEl.style.display = 'none';
        }

        if (signatoryInput.value) {
            document.getElementById('preview-signatory').textContent = signatoryInput.value;
        }
    }

    function replaceVars(text) {
        return text.replace(/\{assessment\}/g, sample['{assessment}'])
                   .replace(/\{name\}/g, sample['{name}'])
                   .replace(/\{first_name\}/g, sample['{first_name}'])
                   .replace(/\{score\}/g, sample['{score}'])
                   .replace(/\{date\}/g, sample['{date}'])
                   .replace(/\{number\}/g, sample['{number}'])
                   .replace(/\{type\}/g, sample['{type}'])
                   .replace(/\{rank\}/g, sample['{rank}']);
    }

    function updateBodyPreview() {
        const text = bodyInput.value.trim();
        if (text) {
            bodyEl.innerHTML = escapeHtml(replaceVars(text)).replace(/\n/g, '<br>');
        } else {
            bodyEl.innerHTML = 'for successfully completing the <strong>' + escapeHtml(sample['{assessment}']) + '</strong>';
        }
    }

    function updateDisclaimerPreview() {
        const text = disclaimerInput.value.trim();
        if (text) {
            disclaimerEl.innerHTML = escapeHtml(replaceVars(text)).replace(/\n/g, '<br>');
        } else {
            disclaimerEl.innerHTML = 'This certificate acknowledges achievement in the ' + escapeHtml(sample['{assessment}']) + ' administered by LabGOTO.';
        }
    }

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    primaryPicker.addEventListener('input', function() { primaryInput.value = this.value; applyPreview(); });
    primaryInput.addEventListener('input', function() { primaryPicker.value = this.value; applyPreview(); });
    secondaryPicker.addEventListener('input', function() { secondaryInput.value = this.value; applyPreview(); });
    secondaryInput.addEventListener('input', function() { secondaryPicker.value = this.value; applyPreview(); });
    bodyInput.addEventListener('input', updateBodyPreview);
    disclaimerInput.addEventListener('input', updateDisclaimerPreview);
    showLogo.addEventListener('change', applyPreview);
    showQr.addEventListener('change', applyPreview);
    signatoryInput.addEventListener('input', applyPreview);

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() { applyPreview(); updateBodyPreview(); updateDisclaimerPreview(); });
    }

    applyPreview();
    updateBodyPreview();
    updateDisclaimerPreview();
});
</script>
@endcan
@endsection
