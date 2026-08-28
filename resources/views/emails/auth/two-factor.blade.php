@extends('emails.layouts.default')

@section('preheader', 'Use this code to finish signing in to your '.config('app.name').' account.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Your Sign-In Code
</h1>

<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    A sign-in attempt was made on your {{ config('app.name') }} administrator account. Enter the code below to finish signing in. This code expires in {{ $expiresIn }} minutes.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; background-color: #f1f5f9; border-radius: 8px;">
    <tr>
        <td align="center" style="padding: 20px 24px;">
            <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #0f172a;">
                {{ $code }}
            </p>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    If you did not attempt to sign in, ignore this email and reset your password immediately.
</p>
@endsection