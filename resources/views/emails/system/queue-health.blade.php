@extends('emails.layouts.default')

@section('preheader', 'The background job queue has accumulated failed jobs that need review.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Queue health alert
</h1>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    <strong>{{ $failedCount }}</strong> queued {{ $failedCount === 1 ? 'job has' : 'jobs have' }} failed in the last {{ $windowHours }} hours. Background work (such as sending email) may be breaking.
</p>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    Inspect the failures with:
</p>

<p style="margin: 0 0 16px; padding: 12px 16px; background-color: #f1f5f9; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; color: #1e293b; word-break: break-word;">
    /opt/alt/php84/usr/bin/php artisan queue:failed
</p>

<p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #64748b;">
    This alert is emitted once per day while the queue stays unhealthy.
</p>
@endsection

@section('footer_note', 'This is an automated operational alert for the Labgoto admin team.')