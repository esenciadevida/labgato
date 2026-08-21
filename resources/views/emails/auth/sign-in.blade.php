@extends('emails.layouts.default')

@section('preheader', 'A new sign-in to your LabGato account was detected.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    New Sign-in to Your Account
</h1>

<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    We noticed a new sign-in to your LabGato account.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; background-color: #f1f5f9; border-radius: 8px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #1e293b;">
                <strong style="color: #64748b;">Date/Time:</strong> {{ $loginTime }}
            </p>
            <p style="margin: 0 0 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #1e293b;">
                <strong style="color: #64748b;">IP Address:</strong> {{ $ipAddress }}
            </p>
            <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #1e293b;">
                <strong style="color: #64748b;">Device:</strong> {{ $userAgent }}
            </p>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    If this was you, no action needed. If you don't recognize this activity, please change your password immediately.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 28px auto;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: #0ea5e9;">
            <a href="{{ url('/account/profile') }}" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                Secure My Account
            </a>
        </td>
    </tr>
</table>
@endsection
