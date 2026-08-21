@extends('emails.layouts.default')

@section('preheader', 'An update on your LabGato contribution — feedback from our review team.')

@section('footer_note', 'We appreciate your effort and encourage you to try again!')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Update on Your Contribution
</h1>

<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    Hi {{ $user->name }}, your {{ $typeLabel }} '{{ $title }}' was not approved.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; background-color: #fef2f2; border-left: 4px solid #f87171; border-radius: 8px;">
    <tr>
        <td style="padding: 16px 20px;">
            <p style="margin: 0 0 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #b91c1c;">
                Reason
            </p>
            <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.5; color: #7f1d1d;">
                {{ $reason }}
            </p>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    You can edit and resubmit based on this feedback.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 28px auto;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: #0ea5e9;">
            <a href="{{ $ctaUrl }}" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                Edit &amp; Resubmit
            </a>
        </td>
    </tr>
</table>
@endsection
