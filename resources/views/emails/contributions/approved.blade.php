@extends('emails.layouts.default')

@section('preheader', 'Great news — your contribution is now live on Labgoto!')

@section('footer_note', 'Thank you for helping build Labgoto!')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Your Contribution Has Been Approved!
</h1>

<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    Great news, {{ $user->name }}! Your {{ $typeLabel }} '{{ $title }}' has been approved and is now live on Labgoto.
</p>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 28px auto;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: #0ea5e9;">
            <a href="{{ $ctaUrl }}" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                View Your Contribution
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0 0 16px; padding: 16px 20px; background-color: #f1f5f9; border-radius: 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.5; color: #1e293b;">
    You've now contributed <strong>{{ $totalContributions }}</strong> {{ Str::plural('resource', $totalContributions) }} to the community.
</p>
@endsection
