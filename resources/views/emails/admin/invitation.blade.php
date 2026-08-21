@extends('emails.layouts.default')

@section('preheader', $isResend ? 'Reminder: your LabGato administrator invitation is still waiting.' : 'You have been invited to join the LabGato admin team.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    @if($isResend)
        Still Waiting for You
    @else
        You're Invited
    @endif
</h1>

<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    {{ $invitedBy }} has invited you to join the LabGato administration team as a
    <strong>{{ $roleName }}</strong>.
</p>

<p style="margin: 0 0 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    To accept this invitation and create your password, click the button below. The link expires on {{ $expiresAt }}.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
    <tr>
        <td align="center">
            <a href="{{ $acceptUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #0ea5e9; color: #ffffff; text-decoration: none; border-radius: 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600;">
                Set Up My Account
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #64748b;">
    If the button doesn't work, copy and paste this link into your browser:<br>
    <a href="{{ $acceptUrl }}" style="color: #1d4ed8; word-break: break-all;">{{ $acceptUrl }}</a>
</p>

<p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #64748b;">
    If you were not expecting this invitation, you can safely ignore this email.
</p>
@endsection
