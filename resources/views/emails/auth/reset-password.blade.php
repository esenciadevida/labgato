@extends('emails.layouts.default')

@section('preheader', 'Reset your Labgoto password — this link expires soon.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Reset Your Password
</h1>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    You requested a password reset for your Labgoto account.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 28px auto;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: #0ea5e9;">
            <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                Reset Password
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #64748b;">
    This link expires in {{ $expireMinutes }} minutes. If you didn't request this, ignore this email and your password will remain unchanged.
</p>

<p style="margin: 0; padding-top: 8px; border-top: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #64748b;">
    For security, this request was made from IP: {{ $ipAddress }}
</p>
@endsection
