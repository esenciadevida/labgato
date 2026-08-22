<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name', 'LabGato') }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .eg-container {
                width: 100% !important;
                border-radius: 0 !important;
            }
            .eg-pad {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .eg-header-pad {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .eg-h1 {
                font-size: 20px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #0f172a; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

{{-- Hidden preheader text shown in inbox previews --}}
<div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
    @yield('preheader', config('app.name', 'LabGato').' — Professional Laboratory Toolkit')
</div>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a;">
    <tr>
        <td align="center" style="padding: 32px 16px;">

            <table role="presentation" class="eg-container" border="0" cellpadding="0" cellspacing="0" width="600" style="width: 600px; max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden;">

                {{-- Header --}}
                <tr>
                    <td class="eg-header-pad" align="center" style="padding: 28px 40px; background-color: #0f172a;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" valign="middle" style="padding-right: 12px;">
                                    <img src="{{ asset('assets/LabGoTo_favicon_192.png') }}" width="36" height="36" alt="Labgoto logo" style="display: block; border: 0; outline: none; text-decoration: none;">
                                </td>
                                <td align="left" valign="middle">
                                    <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.02em;">LabGato</span>
                                </td>
                            </tr>
                        </table>
                        <div style="margin-top: 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #94a3b8;">
                            Professional Laboratory Toolkit
                        </div>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td class="eg-pad" style="padding: 40px; background-color: #ffffff;">
                        @yield('content')
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td class="eg-pad" align="center" style="padding: 24px 40px; background-color: #f1f5f9; border-top: 1px solid #e2e8f0;">
                        <p style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #64748b;">
                            &copy; {{ date('Y') }} {{ config('app.name', 'LabGato') }}. All rights reserved.
                        </p>
                        <p style="margin: 8px 0 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.5; color: #64748b;">
                            @yield('footer_note', 'You received this email because you have a LabGato account.')
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
