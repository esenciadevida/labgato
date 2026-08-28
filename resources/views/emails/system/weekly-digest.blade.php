@extends('emails.layouts.default')

@section('preheader', 'A summary of Labgoto activity for the past week.')

@section('content')
<h1 class="eg-h1" style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 700; line-height: 1.25; color: #1e293b;">
    Weekly digest — {{ $weekStart }} to {{ $weekEnd }}
</h1>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-bottom: 16px;">
    @foreach ($metrics as $label => $value)
    <tr>
        <td style="padding: 10px 16px; border-bottom: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #334155;">
            {{ $label }}
        </td>
        <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; color: #1e293b;">
            {{ $value }}
        </td>
    </tr>
    @endforeach
</table>

<p style="margin: 0 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; color: #1e293b;">
    Detailed analytics are available in the admin dashboard.
</p>

<p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #64748b;">
    This digest is generated automatically each week.
</p>
@endsection

@section('footer_note', 'This is an automated operational summary for the Labgoto admin team.')