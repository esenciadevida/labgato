@extends('layouts.admin')

@section('title', 'Import Preview - ' . config('app.name'))

@section('header', 'Import Preview')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                {{ $validCount }} valid, {{ $invalidCount }} invalid
            </h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Only valid rows will be imported.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('admin.questions.import') }}" variant="secondary">Cancel</x-ui.button>
            @if($validCount > 0)
                <form method="POST" action="{{ route('admin.questions.import.confirm') }}">
                    @csrf
                    <x-ui.button type="submit">Import {{ $validCount }} question(s)</x-ui.button>
                </form>
            @endif
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Line</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Errors</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Subject</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stem</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Correct</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-400">{{ $row['line'] }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($row['valid'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-900/30 dark:text-success-400">
                                        Valid
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-danger-50 px-2.5 py-0.5 text-xs font-medium text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                                        Invalid
                                    </span>
                                @endif
                            </td>
                            <td class="max-w-xs px-4 py-3 text-xs text-danger-600 dark:text-danger-400">
                                @if(count($row['errors']))
                                    <ul class="list-disc space-y-1 pl-3">
                                        @foreach($row['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                @if($row['valid'])
                                    {{ \App\Models\QuestionCategory::find($row['data']['question_category_id'])?->name }}
                                @else
                                    {{ $row['data']['category'] ?? '—' }}
                                @endif
                            </td>
                            <td class="max-w-md px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                {{ Str::limit(strip_tags($row['data']['stem'] ?? ''), 90) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                @if($row['valid'])
                                    {{ chr(65 + (int) $row['data']['correct_index']) }}
                                @else
                                    {{ strtoupper((string) ($row['data']['correct_option'] ?? '—')) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No rows found in the uploaded file.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection
