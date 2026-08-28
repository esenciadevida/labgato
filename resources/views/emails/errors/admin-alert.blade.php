@extends('emails.layouts.default')

@section('preheader', 'An error was thrown on the Labgoto site and needs your attention.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Error: {{ class_basename($exceptionClass) }}
</h1>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;">
    <tr>
        <td style="padding: 12px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #1e293b; word-break: break-word;">
            <strong>Message:</strong> {{ $exceptionMessage ?: '(no message)' }}
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 16px; border-top: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #334155; word-break: break-word;">
            <strong>Location:</strong> {{ $file }}:{{ $line }}
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 16px; border-top: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #334155; word-break: break-word;">
            <strong>Request:</strong> {{ $method ?? '—' }} {{ $url ?? '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 16px; border-top: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #334155; word-break: break-word;">
            <strong>Browser:</strong> {{ $userAgent ?? '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding: 12px 16px; border-top: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #334155; word-break: break-word;">
            <strong>User ID:</strong> {{ $userId ?? 'guest (not signed in)' }}
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #64748b;">
    Similar errors are grouped and suppressed for 15 minutes to avoid flooding this inbox.
</p>
@endsection

@section('footer_note', 'This is an automated operational alert for the Labgoto admin team.')