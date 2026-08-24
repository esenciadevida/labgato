<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1e293b;
            background: #ffffff;
        }

        .frame {
            width: 100%;
            height: 100%;
            border: 4px solid #1e3a8a;
            padding: 6px;
        }

        .frame-inner {
            border: 2px solid #d4af37;
            padding: 28px 40px;
            height: 100%;
            text-align: center;
        }

        .wordmark {
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #1e3a8a;
        }

        .tagline {
            font-size: 11px;
            letter-spacing: 3px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .type-label {
            display: inline-block;
            margin-top: 22px;
            padding: 6px 18px;
            border: 1px solid #d4af37;
            color: #85700f;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .presented {
            margin-top: 26px;
            font-size: 13px;
            color: #64748b;
            font-style: italic;
        }

        .holder {
            margin-top: 12px;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 38px;
            color: #0f172a;
        }

        .completion {
            margin-top: 14px;
            font-size: 15px;
            color: #334155;
        }

        .assessment-title {
            font-weight: bold;
            color: #1e3a8a;
        }

        .score-line {
            margin-top: 10px;
            font-size: 13px;
            color: #475569;
        }

        .meta-line {
            margin-top: 16px;
            font-size: 11px;
            color: #64748b;
        }

        .footer {
            margin-top: 34px;
            table-layout: fixed;
            width: 100%;
        }

        .footer td { vertical-align: bottom; }

        .signature-cell {
            text-align: left;
            width: 55%;
        }

        .qr-cell {
            text-align: right;
            width: 45%;
        }

        .signature-name {
            border-top: 1px solid #94a3b8;
            display: inline-block;
            padding-top: 6px;
            min-width: 220px;
            font-size: 13px;
            font-weight: bold;
            color: #334155;
        }

        .signature-role {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }

        .qr-code {
            width: 96px;
            height: 96px;
            display: block;
            margin-left: auto;
        }

        .qr-caption {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }

        .verification-code {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #334155;
        }

        .disclaimer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 8.5px;
            line-height: 1.5;
            color: #94a3b8;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="frame-inner">
            <div class="wordmark">LabGOTO</div>
            <div class="tagline">Laboratory Toolkit</div>

            <div class="type-label">{{ $certificate->label() }}</div>

            <div class="presented">This is proudly presented to</div>
            <div class="holder">{{ $holderName }}</div>

            <div class="completion">
                for successfully completing the
                <span class="assessment-title">{{ $certificate->assessment->title }}</span>
            </div>

            @if($certificate->type !== 'participation')
                <div class="score-line">
                    Score: {{ number_format($certificate->score_percent, 1) }}%
                    @if($certificate->rank_at_issue !== null)
                        &nbsp;&middot;&nbsp; Rank: #{{ $certificate->rank_at_issue }}
                    @endif
                </div>
            @endif

            <div class="meta-line">
                Issued on {{ $certificate->issued_at->format('F j, Y') }}
                &nbsp;&middot;&nbsp; Certificate No. {{ $certificate->certificate_number }}
            </div>

            <table class="footer">
                <tr>
                    <td class="signature-cell">
                        <div class="signature-name">Program Director</div>
                        <div class="signature-role">Program Director, LabGOTO</div>
                    </td>
                    <td class="qr-cell">
                        @if($qrBase64)
                            <img class="qr-code" src="data:image/png;base64,{{ $qrBase64 }}" alt="Verification QR code">
                            <div class="qr-caption">Scan to verify</div>
                            <div class="verification-code">{{ $certificate->verification_code }}</div>
                        @else
                            <div class="qr-caption">
                                Verify at<br>
                                <span class="verification-code">{{ $certificate->verifyUrl() }}</span><br>
                                Code: {{ $certificate->verification_code }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="disclaimer">
                This certificate is issued by LabGOTO in recognition of performance in the
                {{ $certificate->assessment->title }}. It is a LabGOTO achievement certificate and is NOT an ASCP
                certification nor an official ASCP credential.
            </div>
        </div>
    </div>
</body>
</html>
