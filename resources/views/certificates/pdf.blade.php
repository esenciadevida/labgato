<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 0; }

        * { margin: 0; padding: 0; }

        body { font-family: Helvetica, Arial, sans-serif; color: #0f172a; background: #ffffff; }

        .certificate { position: relative; width: 280mm; height: 184mm; margin: 8mm 7mm; border: 5px solid #1e3a8a; overflow: hidden; background: #fffdf6; }

        .certificate-inner { position: relative; margin: 3mm; border: 1px solid #d4af37; }

        .content { padding: 8mm 15mm; text-align: center; }

        .top-rule {
            width: 60mm; height: 1px; margin: 0 auto 3mm; background: #d4af37;
        }

        .cert-header { width: 100%; border-collapse: collapse; }

        .wordmark { font-size: 18px; font-weight: 800; letter-spacing: 3px; color: #1e3a8a; text-align: left; }
        .wordmark small {
            display: block; font-size: 6px; font-weight: 600; letter-spacing: 2px;
            color: #64748b; text-transform: uppercase; margin-top: 1px;
        }

        .cert-no { font-size: 7.5px; color: #64748b; text-align: right; line-height: 1.4; }
        .cert-no strong { color: #334155; }

        .type-label {
            display: inline-block; margin-top: 3mm; padding: 2.4mm 10mm;
            border: 1px solid #b98a12; border-radius: 999px; color: #7a5e0c;
            font-size: 8.5px; font-weight: 800; letter-spacing: 3px;
            text-transform: uppercase; background: #fcf3d9;
        }

        .presented { margin-top: 4mm; font-size: 9.5px; color: #64748b; font-style: italic; }
        .holder { font-family: Georgia, 'Times New Roman', serif; font-size: 36px; line-height: 1.06; color: #0f172a; }

        .holder-underline { width: 90mm; height: 1px; margin: 2mm auto 0; background: #d4af37; }

        .completion { margin-top: 3.5mm; font-size: 10.5px; color: #334155; }
        .assessment-title { font-weight: 800; color: #1e3a8a; }

        .score-line { margin-top: 2.5mm; font-size: 9.5px; color: #475569; }
        .score-line strong { color: #0f172a; }
        .meta-line { margin-top: 3mm; font-size: 8px; color: #64748b; }

        .footer { width: 100%; margin-top: 6mm; border-top: 1px solid #d4af37; padding-top: 3mm; border-collapse: collapse; }
        .footer td { vertical-align: bottom; }
        .signature-cell { text-align: left; width: 52%; padding-left: 0; }
        .qr-cell { text-align: right; width: 48%; }

        .signature-img { max-width: 164px; max-height: 32px; display: block; margin-bottom: 1px; }
        .signature-line { width: 186px; border-top: 1px solid #94a3b8; padding-top: 1mm; }
        .signature-name { font-family: Georgia, serif; font-size: 12px; font-weight: 700; color: #334155; display: block; }
        .signature-role { font-size: 7px; color: #64748b; display: block; margin-top: 0.4mm; text-transform: uppercase; letter-spacing: 0.8px; }

        .qr-code { width: 96px; height: 96px; }
        .qr-caption { font-size: 7px; color: #64748b; margin-top: 1mm; line-height: 1.5; }
        .verification-code { display: inline-block; font-size: 9.5px; font-weight: 800; letter-spacing: 2px; color: #334155; margin-top: 1mm; }

        .disclaimer {
            margin-top: 2mm; border-top: 1px solid #e2e8f0; padding-top: 1.6mm;
            font-size: 6.4px; line-height: 1.4; color: #94a3b8; text-align: justify;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="certificate-inner">
            <div class="content">
                <table class="cert-header">
                    <tr>
                        <td style="text-align:left;vertical-align:top;">
                            <div class="wordmark">LabGOTO<small>Medical Laboratory Knowledge Platform</small></div>
                        </td>
                        <td style="text-align:right;vertical-align:top;">
                            <div class="cert-no">Certificate No.<br><strong>{{ $certificate->certificate_number }}</strong></div>
                        </td>
                    </tr>
                </table>

                <div class="top-rule"></div>

                <div class="type-label">{{ $certificate->label() }}</div>

                <div class="presented">This certificate is proudly presented to</div>
                <div class="holder">{{ $holderName }}</div>
                <div class="holder-underline"></div>

                <div class="completion">
                    for successfully completing the
                    <span class="assessment-title">{{ $certificate->assessment->title }}</span>
                </div>

                @if($certificate->type !== 'participation')
                    <div class="score-line">
                        Score <strong>{{ number_format($certificate->score_percent, 1) }}%</strong>
                        @if($certificate->rank_at_issue !== null)
                            &nbsp;&middot;&nbsp; Rank <strong>#{{ $certificate->rank_at_issue }}</strong>
                        @endif
                    </div>
                @endif

                <div class="meta-line">
                    Issued on {{ $certificate->issued_at->format('F j, Y') }} &middot; Verification Code {{ $certificate->verification_code }}
                </div>

                <table class="footer">
                    <tr>
                        <td class="signature-cell">
                            @if(!empty($signatureBase64))
                                <img class="signature-img" src="data:image/png;base64,{{ $signatureBase64 }}" alt="Authorised signature">
                            @endif
                            <div class="signature-line">
                                <span class="signature-name">{{ $signatoryName }}</span>
                                <span class="signature-role">{{ $signatoryTitle }}</span>
                            </div>
                        </td>
                        <td class="qr-cell">
                            @if($qrBase64)
                                <img class="qr-code" src="data:image/png;base64,{{ $qrBase64 }}" alt="Verification QR code">
                            @endif
                            <div class="qr-caption">Scan to verify this certificate at<br>labgoto.com/verify/{{ $certificate->verification_code }}</div>
                            <div class="verification-code">{{ $certificate->verification_code }}</div>
                        </td>
                    </tr>
                </table>

                <div class="disclaimer">
                    This certificate acknowledges achievement in the {{ $certificate->assessment->title }} administered by LabGOTO.
                    It is not an ASCP certification nor an official ASCP credential. Verify authenticity at the QR code above or at
                    {{ $certificate->verifyUrl() }}.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
