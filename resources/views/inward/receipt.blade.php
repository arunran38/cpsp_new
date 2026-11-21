<!DOCTYPE html>
<html lang="ml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>കൈപ്പറ്റ് രസീത് - {{ $petition->receipt_no }}</title>

    <!-- Google Fonts for Malayalam and English -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Malayalam:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Noto Sans Malayalam', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
            background-color: #e2e8f0;
        }

        /* Screen Toolbar */
        @media screen {
            .screen-toolbar {
                position: sticky;
                top: 0;
                z-index: 50;
                background: #002855;
                color: #ffffff;
                padding: 9px 24px;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            }

            .toolbar-actions {
                display: flex;
                gap: 12px;
                align-items: center;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 9px 20px;
                border-radius: 8px;
                font-size: 13.5px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s ease;
                border: none;
                text-decoration: none;
            }

            .btn-primary {
                background: #ffb800;
                color: #002855;
                box-shadow: 0 2px 8px rgba(255, 184, 0, 0.4);
            }

            .btn-primary:hover {
                background: #f59e0b;
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: rgba(255, 255, 255, 0.15);
                color: #ffffff;
                border: 1px solid rgba(255, 255, 255, 0.25);
            }

            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.25);
            }

            .screen-container {
                padding: 30px 15px;
                display: flex;
                justify-content: center;
            }

            .page-a4 {
                width: 210mm;
                height: 297mm;
                background: #ffffff;
                box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.25), 0 5px 15px -3px rgba(0, 0, 0, 0.15);
                border: 1px solid #cbd5e1;
                display: flex;
                flex-direction: column;
                position: relative;
            }
        }

        /* Unused Lower Half of A4 (Completely Blank) */
        .receipt-lower-half {
            width: 210mm;
            height: 148.5mm;
            background: #ffffff;
        }

        /* Upper Half (210mm x 148.5mm) */
        .receipt-upper-half {
            width: 210mm;
            height: 148.5mm;
            max-height: 148.5mm;
            padding: 10mm 14mm 3mm 14mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background: #ffffff;
            position: relative;
            box-sizing: border-box;
        }

        /* Main Card Frame (matches official blue rounded border) */
        .official-card {
            width: 100%;
            height: 126mm;
            border: 2.5px solid #003366;
            border-radius: 18px;
            padding: 6mm 8mm 5mm 8mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            margin-bottom: 3.5mm;
        }

        /* Decorative bottom corner swooshes */
        .corner-swoosh-left {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 95px;
            height: 48px;
            pointer-events: none;
            z-index: 1;
        }

        .corner-swoosh-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 95px;
            height: 48px;
            pointer-events: none;
            z-index: 1;
            transform: scaleX(-1);
        }

        /* HEADER SECTION */
        .header-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 0 1mm;
            position: relative;
            z-index: 2;
        }

        .logo-wrap {
            width: 82px;
            height: 82px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.08));
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .dept-title-malayalam {
            font-size: 22.5px;
            font-weight: 900;
            color: #002b5b;
            line-height: 1.15;
            letter-spacing: -0.01em;
        }

        .dept-title-english {
            font-size: 10.5px;
            font-weight: 800;
            color: #003366;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 2.5px;
            font-family: 'Inter', sans-serif;
        }

        /* Header Contact Pill */
        .header-contact-pill {
            margin-top: 4px;
            background: #e2f0fb;
            border: 1px solid #bfdbfe;
            border-radius: 9999px;
            padding: 2.5px 14px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            font-weight: 600;
            color: #002b5b;
            white-space: nowrap;
            flex-wrap: nowrap;
        }

        .header-contact-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .header-pill-divider {
            color: #93c5fd;
            font-weight: 300;
            user-select: none;
            flex-shrink: 0;
        }

        .icon-circle-blue {
            width: 18px;
            height: 18px;
            background: #002b5b;
            color: #ffffff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-circle-blue svg {
            width: 10px;
            height: 10px;
            fill: currentColor;
        }

        .mail-icon-svg {
            width: 15px;
            height: 15px;
            stroke: #002b5b;
            stroke-width: 2.2;
            fill: none;
        }

        .header-pill-divider {
            color: #94a3b8;
            font-weight: 400;
            font-size: 11px;
        }

        /* TITLE RIBBON (കൈപ്പറ്റ് രസീത്) */
        .ribbon-section {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 2mm 0 1.5mm 0;
            z-index: 2;
        }

        .ribbon-line {
            flex-grow: 1;
            height: 2px;
            background: #ffb800;
        }

        .ribbon-accents {
            color: #ffb800;
            font-size: 21px;
            font-weight: 900;
            font-style: italic;
            letter-spacing: -2px;
            padding: 0 8px;
            user-select: none;
            line-height: 1;
        }

        .ribbon-badge {
            background: linear-gradient(135deg, #002b5b 0%, #004b87 100%);
            color: #ffffff;
            padding: 4px 24px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 3px 8px rgba(0, 43, 91, 0.25);
            border: 1.5px solid #ffffff;
        }

        .ribbon-icon-circle {
            width: 25px;
            height: 25px;
            background: #ffffff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #002b5b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

        .ribbon-title-text {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 0.02em;
            color: #ffffff;
        }

        /* RECEIPT BODY CONTENT */
        .body-section {
            padding: 0 8mm;
            position: relative;
            z-index: 2;
        }

        .number-box {
            display: inline-flex;
            align-items: center;
            background: #dbeafe;
            padding: 4px 18px 4px 16px;
            border-radius: 9999px;
            margin-bottom: 3.5mm;
        }

        .number-label {
            font-size: 14.5px;
            font-weight: 800;
            color: #002b5b;
            margin-right: 6px;
        }

        .number-dots {
            font-size: 15.5px;
            font-weight: 800;
            font-family: 'Inter', sans-serif;
            color: #002b5b;
            letter-spacing: 0.03em;
        }

        .receipt-statement-para {
            font-size: 14.5px;
            font-weight: 600;
            color: #0f172a;
            line-height: 2;
            text-align: justify;
            text-justify: inter-word;
            text-align-last: left;
            text-indent: 36px;
            margin: 0;
            padding: 0;
        }

        .statement-highlight {
            font-size: 15px;
            font-weight: 800;
            color: #002b5b;
            background-color: #eef6ff;
            border-bottom: 2px solid #002b5b;
            border-radius: 2px;
            padding: 0 4px 1px 4px;
            display: inline;
            box-decoration-break: clone;
            -webkit-box-decoration-break: clone;
            line-height: 1.3;
        }

        .statement-highlight.complainant-font {
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .statement-highlight.date-font {
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.03em;
            display: inline-block;
            white-space: nowrap;
            text-align: center !important;
            text-align-last: center !important;
            text-indent: 0 !important;
            min-width: 110px;
            padding: 0 8px 1px 8px;
            box-sizing: border-box;
            vertical-align: baseline;
        }

        /* SEAL & SIGNATURE SECTION */
        .seal-signature-wrapper {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 0 8mm;
            position: relative;
            z-index: 2;
        }

        /* Blank circular area on the left for manual official round seal stamping (No borders, image, or outline) */
        .manual-seal-area {
            width: 42mm;
            height: 32mm;
            flex-shrink: 0;
        }

        .signature-box {
            text-align: center;
            min-width: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-signing-space {
            width: 100%;
            height: 14mm;
        }

        .signature-line-mark {
            width: 100%;
            height: 1px;
            border-bottom: 1.5px solid #002b5b;
            margin-bottom: 4px;
        }

        .signature-caption {
            font-size: 13px;
            font-weight: 800;
            color: #002b5b;
            white-space: nowrap;
        }

        /* FOOTER CONTACT BAR */
        .footer-bar-container {
            position: relative;
            z-index: 2;
            margin-top: 2mm;
            display: flex;
            justify-content: center;
        }

        .footer-contact-bar {
            background: #002b5b;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0, 43, 91, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .footer-enquiry-pill {
            background: #ffb800;
            color: #002b5b;
            padding: 5px 16px 5px 14px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
        }

        .enquiry-caption {
            font-size: 13.5px;
            font-weight: 900;
            color: #002b5b;
            white-space: nowrap;
        }

        .footer-numbers-pill {
            padding: 5px 20px 5px 16px;
            display: inline-flex;
            align-items: center;
            gap: 16px;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
        }

        .footer-number-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .gold-glyph {
            width: 18px;
            height: 18px;
            background: #ffb800;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #002b5b;
            flex-shrink: 0;
        }

        .gold-glyph svg {
            width: 10px;
            height: 10px;
            fill: currentColor;
        }

        .toll-free-tag {
            font-size: 12px;
            font-weight: 500;
            color: #cbd5e1;
            margin-left: 2px;
        }

        .footer-divider-pipe {
            color: #64748b;
            font-weight: 300;
        }

        /* CUTTING GUIDE LINE */
        .cut-line-wrapper {
            width: 100%;
            height: 6mm;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            user-select: none;
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .cut-line {
            width: 100%;
            border-bottom: 1.5px dashed #94a3b8;
            position: absolute;
            top: 50%;
            left: 0;
            z-index: 1;
        }

        .cut-badge {
            position: relative;
            z-index: 2;
            background: #ffffff;
            padding: 0 12px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cut-badge svg {
            width: 12px;
            height: 12px;
        }

        /* PRINT STYLES */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .no-print,
            .screen-toolbar {
                display: none !important;
            }

            .screen-container {
                padding: 0 !important;
                margin: 0 !important;
            }

            .page-a4 {
                width: 210mm !important;
                height: 297mm !important;
                max-height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
            }

            .receipt-upper-half {
                width: 210mm !important;
                height: 148.5mm !important;
                max-height: 148.5mm !important;
                padding: 10mm 14mm 3mm 14mm !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                box-sizing: border-box !important;
            }

            .official-card {
                width: 100% !important;
                height: 126mm !important;
                padding: 6mm 8mm 5mm 8mm !important;
                box-sizing: border-box !important;
                margin-bottom: 3.5mm !important;
            }

            .receipt-lower-half {
                width: 210mm !important;
                height: 148.5mm !important;
                background: #ffffff !important;
            }

            .logo-wrap img {
                filter: none !important;
            }

            .header-contact-pill {
                white-space: nowrap !important;
                flex-wrap: nowrap !important;
            }
        }
    </style>
</head>

<body>

    <!-- Action Toolbar (Visible on screen, hidden on print) -->
    <div class="screen-toolbar no-print">
        <div class="toolbar-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Print Receipt
            </button>
        </div>
    </div>

    @php
        $complainants = $petition->displayAddresses()->where('person_type', 'Complainant')->unique('person_name');
        $complainantNames = $complainants->pluck('person_name')->filter()->implode(', ');
        $formattedDate = $petition->date_of_petition_received
            ? \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d/m/Y')
            : ($petition->created_at ? $petition->created_at->format('d/m/Y') : date('d/m/Y'));
    @endphp

    <!-- Screen Container with A4 Page -->
    <div class="screen-container">
        <div class="page-a4">

            <!-- UPPER HALF: EXACT HALF A4 (A5: 210mm x 148.5mm) -->
            <div class="receipt-upper-half">

                <!-- OFFICIAL CARD -->
                <div class="official-card">

                    <!-- Decorative Corner Wave Accents -->
                    <svg class="corner-swoosh-left" viewBox="0 0 110 55" preserveAspectRatio="none">
                        <path d="M0,55 L0,15 Q35,48 110,55 Z" fill="#0284c7" opacity="0.95" />
                        <path d="M0,55 L0,28 Q30,52 80,55 Z" fill="#ffb800" />
                    </svg>
                    <svg class="corner-swoosh-right" viewBox="0 0 110 55" preserveAspectRatio="none">
                        <path d="M0,55 L0,15 Q35,48 110,55 Z" fill="#0284c7" opacity="0.95" />
                        <path d="M0,55 L0,28 Q30,52 80,55 Z" fill="#ffb800" />
                    </svg>

                    <!-- HEADER SECTION -->
                    <div class="header-section">
                        <div class="logo-wrap">
                            <img src="{{ asset('images/vacb-logo.png') }}?v={{ file_exists(public_path('images/vacb-logo.png')) ? filemtime(public_path('images/vacb-logo.png')) : '1' }}"
                                alt="Government of Kerala - Vigilance &amp; Anti-Corruption Bureau Emblem">
                        </div>
                        <div class="header-content">
                            <div class="dept-title-malayalam">അഴിമതി നിരോധന ബ്യൂറോ ഡയറക്ടറേറ്റ്</div>
                            <div class="dept-title-english">VIGILANCE &amp; ANTI-CORRUPTION BUREAU DIRECTORATE,
                                THIRUVANANTHAPURAM</div>
                            <div class="header-contact-pill">
                                <div class="header-contact-item">
                                    <span class="icon-circle-blue">
                                        <svg viewBox="0 0 24 24">
                                            <path
                                                d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24 11.72 11.72 0 003.68.59 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.72 11.72 0 00.59 3.68 1 1 0 01-.24 1.02l-2.23 2.09z" />
                                        </svg>
                                    </span>
                                    <span><strong>PHONE:</strong> 0471-2305393, 0471-2305033</span>
                                </div>
                                <span class="header-pill-divider">|</span>
                                <div class="header-contact-item">
                                    <svg class="mail-icon-svg" viewBox="0 0 24 24">
                                        <rect width="20" height="16" x="2" y="4" rx="2" />
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                    </svg>
                                    <span><strong>E-MAIL:</strong> vig.vacb@kerala.gov.in</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TITLE RIBBON (കൈപ്പറ്റ് രസീത്) -->
                    <div class="ribbon-section">
                        <div class="ribbon-line"></div>
                        <div class="ribbon-accents">//</div>
                        <div class="ribbon-badge">
                            <div class="ribbon-icon-circle">
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#002b5b"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="9" y1="13" x2="15" y2="13"></line>
                                    <line x1="9" y1="17" x2="13" y2="17"></line>
                                    <circle cx="16" cy="17" r="3" fill="#10b981" stroke="#ffffff" stroke-width="1.5">
                                    </circle>
                                    <path d="m15 17 1 1 2-2" stroke="#ffffff" stroke-width="1.5"></path>
                                </svg>
                            </div>
                            <span class="ribbon-title-text">കൈപ്പറ്റ് രസീത്</span>
                        </div>
                        <div class="ribbon-accents">//</div>
                        <div class="ribbon-line"></div>
                    </div>

                    <!-- RECEIPT STATEMENT BODY -->
                    <div class="body-section">
                        <!-- Receipt Number Pill -->
                        <div class="number-box">
                            <span class="number-label">നമ്പർ :</span>
                            <span class="number-dots">{{ $petition->receipt_no }}</span>
                        </div>

                        <!-- Acknowledgement Statement (Single Unified Justified Paragraph) -->
                        <p class="receipt-statement-para">
                            ശ്രീ / ശ്രീമതി <strong
                                class="statement-highlight complainant-font">{{ $complainantNames ?: '—' }}</strong>
                            -ന്റെ
                            പക്കൽ നിന്നും <strong class="statement-highlight date-font">{{ $formattedDate }}</strong>
                            തീയതിയിൽ പരാതി
                            കൈപ്പറ്റിയിരിക്കുന്നു. ഇതിന്മേൽ തുടർന്നുള്ള എഴുത്തുകുത്തുകളിൽ ഈ രസീതിലെ നമ്പർ ദയവായി
                            സൂചിപ്പിക്കേണ്ടതാണ്.
                        </p>
                    </div>

                    <!-- SEAL & SIGNATURE SECTION -->
                    <div class="seal-signature-wrapper">
                        <!-- Adequate blank circular area on left reserved for manually stamping official round seal (No borders/placeholders) -->
                        <div class="manual-seal-area" aria-hidden="true"></div>

                        <!-- Officer Signature on right with ample room above line for manual signing -->
                        <div class="signature-box">
                            <div class="signature-signing-space"></div>
                            <div class="signature-line-mark"></div>
                            <div class="signature-caption">ബന്ധപ്പെട്ട ഉദ്യോഗസ്ഥന്റെ ഒപ്പ്</div>
                        </div>
                    </div>

                    <!-- FOOTER CONTACT BAR (Inside the receipt border) -->
                    <div class="footer-bar-container">
                        <div class="footer-contact-bar">
                            <div class="footer-enquiry-pill">
                                <span class="icon-circle-blue">
                                    <svg viewBox="0 0 24 24">
                                        <path
                                            d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24 11.72 11.72 0 003.68.59 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.72 11.72 0 00.59 3.68 1 1 0 01-.24 1.02l-2.23 2.09z" />
                                    </svg>
                                </span>
                                <span class="enquiry-caption">അന്വേഷണങ്ങൾക്ക് :</span>
                            </div>
                            <div class="footer-numbers-pill">
                                <div class="footer-number-group">
                                    <span class="gold-glyph">
                                        <svg viewBox="0 0 24 24">
                                            <path
                                                d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24 11.72 11.72 0 003.68.59 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.72 11.72 0 00.59 3.68 1 1 0 01-.24 1.02l-2.23 2.09z" />
                                        </svg>
                                    </span>
                                    <span>1064</span>
                                    <span class="toll-free-tag">(Toll Free)</span>
                                </div>
                                <span class="footer-divider-pipe">|</span>
                                <div class="footer-number-group">
                                    <span class="gold-glyph">
                                        <svg viewBox="0 0 24 24">
                                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2" fill="none"
                                                stroke="currentColor" stroke-width="2" />
                                            <circle cx="12" cy="18" r="1" />
                                        </svg>
                                    </span>
                                    <span>8592900900</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CUTTING LINE (Separation Line) -->
                <div class="cut-line-wrapper">
                    <div class="cut-line"></div>
                    <div class="cut-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="6" r="3"></circle>
                            <circle cx="6" cy="18" r="3"></circle>
                            <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                            <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                            <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                        </svg>
                        <span>Cut along this line</span>
                    </div>
                </div>

            </div>

            <!-- LOWER HALF: EXACTLY HALF A4 (COMPLETELY BLANK) -->
            <div class="receipt-lower-half"></div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }
            window.focus();
        });

        // Automatically trigger print dialog when opened
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 350);
        });

        // Use Esc key for exit
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
                e.preventDefault();
                window.close();
                setTimeout(function () {
                    if (!window.closed && window.history.length > 1) {
                        window.history.back();
                    }
                }, 100);
            }
        });
    </script>
</body>

</html>