@extends('emails.layouts.default')

@section('preheader', $created ? 'A password was created for your LabGato account.' : 'The password for your LabGato account was changed.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    @if($created)
        Your Password Was Created
    @else
        Your Password Was Changed
    @endif
</h1>

@if($created)
<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    A password was just created for your LabGato account. You can now sign in with your email address and password, or continue using Google sign-in — both will work.
</p>
@else
<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    The password for your LabGato account was just changed. All other devices have been signed out.
</p>
@endif

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; background-color: #f1f5f9; border-radius: 8px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #1e293b;">
                <strong style="color: #64748b;">Date/Time:</strong> {{ $changedAt }}
            </p>
            <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #1e293b;">
                <strong style="color: #64748b;">IP Address:</strong> {{ $ipAddress }}
            </p>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    If this was you, no action needed. If you don't recognize this activity, please reset your password immediately to secure your account.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 28px auto;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: #0ea5e9;">
            <a href="{{ route('auth.password.request') }}" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                Reset My Password
            </a>
        </td>
    </tr>
</table>
@endsection
