@extends('emails.layouts.default')

@section('preheader', 'The LabGato team has replied to your message.')

@section('footer_note')
    You can reply directly to this email or visit your messages.
@endsection

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Reply to Your Message
</h1>

<p style="margin: 0 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    Hi {{ $userName }}, an administrator has replied to your message.
</p>

{{-- Original message --}}
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px; background-color: #f8fafc; border-left: 4px solid #cbd5e1; border-radius: 8px;">
    <tr>
        <td style="padding: 16px 20px;">
            <p style="margin: 0 0 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                Your message
            </p>
            <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; font-style: italic; color: #475569;">
                "{{ $originalMessage }}"
            </p>
        </td>
    </tr>
</table>

{{-- Admin reply --}}
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px; background-color: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 8px;">
    <tr>
        <td style="padding: 16px 20px;">
            <p style="margin: 0 0 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #0369a1;">
                LabGato team
            </p>
            <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.5; font-weight: 600; color: #1e293b;">
                {{ $reply }}
            </p>
        </td>
    </tr>
</table>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 28px auto;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: #0ea5e9;">
            <a href="{{ $messagesUrl }}" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                View Full Conversation
            </a>
        </td>
    </tr>
</table>
@endsection
