<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 0; }

        * { margin: 0; padding: 0; }

        body { font-family: Helvetica, Arial, sans-serif; color: #1f2937; background: #ffffff; }

        .certificate {
            position: relative;
            width: 280mm;
            height: 184mm;
            margin: 8mm 7mm;
            border: 5px solid #1e3a8a;
            overflow: hidden;
            background: #ffffff;
        }

        /* Gold frame extends the full certificate height. */
        .frame-gold {
            position: absolute;
            inset: 3mm;
            border: 1.5px solid #d4af37;
            pointer-events: none;
        }

        .content { padding: 10mm 16mm; text-align: center; }

        .header { width: 100%; margin-bottom: 5mm; }

        .logo { width: 44mm; height: auto; display: block; }

        .tagline {
            margin-top: 1mm; font-size: 6px; font-weight: 600; letter-spacing: 2px;
            color: #64748b; text-transform: uppercase;
        }

        .wordmark { font-size: 19px; font-weight: 800; letter-spacing: 2px; color: #1e3a8a; line-height: 1.1; }
        .wordmark small {
            display: block; font-size: 6px; font-weight: 600; letter-spacing: 2px;
            color: #64748b; text-transform: uppercase; margin-top: 1px;
        }

        .cert-meta { text-align: right; font-size: 7.5px; color: #64748b; line-height: 1.5; }
        .cert-meta strong { color: #334155; font-weight: 700; }

        .clear { clear: both; }

        .type-ribbon {
            display: inline-block; padding: 2.4mm 10mm; margin-top: 4mm;
            border: 1px solid #b98a12; border-radius: 999px;
            color: #6b5320; font-size: 8.5px; font-weight: 800; letter-spacing: 3px;
            text-transform: uppercase; background: #ffffff;
        }

        .presented { margin-top: 5mm; font-size: 10px; color: #6b7280; font-style: italic; letter-spacing: 0.5px; }

        .holder {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 40px; font-weight: bold; line-height: 1.08; color: #1e3a8a;
        }

        .holder-rule { width: 120mm; height: 1px; margin: 2mm auto 0; background: #d4af37; }

        .completion { margin-top: 4mm; font-size: 10.5px; color: #374151; line-height: 1.5; }
        .completion .title { font-weight: 700; color: #1e3a8a; }

        .score-line { margin-top: 3mm; font-size: 9.5px; color: #475569; }
        .score-line strong { color: #111827; }

        .footer { width: 100%; margin-top: 13mm; padding-top: 4mm; border-top: 1px solid #d4af37; border-collapse: collapse; }
        .signature-cell { text-align: left; width: 55%; vertical-align: bottom; padding: 0; }
        .verify-cell { text-align: right; width: 45%; vertical-align: bottom; padding: 0; }

        .signature-img { width: 150px; height: 28px; object-fit: contain; display: block; margin: 0 0 1px; }
        .signature-line { width: 190px; border-top: 1px solid #94a3b8; padding-top: 1.2mm; }
        .signature-name { font-family: Georgia, serif; font-size: 12px; font-weight: 700; color: #334155; display: block; }
        .signature-role { font-size: 7px; color: #64748b; display: block; margin-top: 0.5mm; text-transform: uppercase; letter-spacing: 0.8px; }

        .verify-block { display: inline-block; text-align: center; background: #ffffff; padding: 2mm 0 0; }
        .qr-code { width: 92px; height: 92px; display: block; margin: 0 auto; }
        .verify-line { font-size: 7.5px; color: #374151; margin-top: 1.5mm; line-height: 1.5; }
        .verify-code { display: block; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; color: #111827; margin-top: 0.8mm; }

        .disclaimer {
            margin-top: 5mm; padding-top: 2mm; border-top: 1px solid #e2e8f0;
            font-size: 8px; line-height: 1.55; color: #475569; text-align: justify;
            max-width: 240mm; margin-left: auto; margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="frame-gold"></div>
        <div class="content">
            <table class="header">
                <tr>
                    <td style="text-align:left;vertical-align:top;">
                        @if(!empty($logoBase64))
                            <img class="logo" src="data:image/png;base64,{{ $logoBase64 }}" alt="LabGOTO">
                            <div class="tagline">Medical Laboratory Knowledge Platform</div>
                        @else
                            <div class="wordmark">LabGOTO<small>Medical Laboratory Knowledge Platform</small></div>
                        @endif
                    </td>
                    <td style="text-align:right;vertical-align:top;">
                        <div class="cert-meta">
                            Certificate No.<br><strong>{{ $certificate->certificate_number }}</strong><br>
                            Issued {{ $certificate->issued_at->format('M j, Y') }}
                        </div>
                    </td>
                </tr>
            </table>
            <div class="clear"></div>

            <div class="type-ribbon">{{ $certificate->label() }}</div>

            <div class="presented">This certificate is proudly presented to</div>
            <div class="holder">{{ $holderName }}</div>
            <div class="holder-rule"></div>

            <div class="completion">
                for successfully completing the
                <span class="title">{{ $certificate->assessment->title }}</span>
            </div>

            @if($certificate->type !== 'participation')
                <div class="score-line">
                    Score <strong>{{ number_format($certificate->score_percent, 1) }}%</strong>
                    @if($certificate->rank_at_issue !== null)
                        &nbsp;&middot;&nbsp; Rank <strong>#{{ $certificate->rank_at_issue }}</strong>
                    @endif
                </div>
            @endif

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
                    <td class="verify-cell">
                        <div class="verify-block">
                            @if($qrBase64)
                                <img class="qr-code" src="data:image/png;base64,{{ $qrBase64 }}" alt="Verification QR code">
                            @endif
                            <p class="verify-line">Scan or visit<br><strong>labgoto.com/verify</strong></p>
                            <span class="verify-code">Code: {{ $certificate->verification_code }}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="disclaimer">
                This certificate acknowledges achievement in the {{ $certificate->assessment->title }} administered by LabGOTO.
                It is not an ASCP certification nor an official ASCP credential. Verify authenticity by scanning the QR code above
                or visiting {{ $certificate->verifyUrl() }}.
            </div>
        </div>
    </div>
</body>
</html>
