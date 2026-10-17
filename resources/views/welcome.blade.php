<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Centralized Processing System of Petitions - Official Portal of Vigilance & Anti-Corruption Bureau, Government of Kerala">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CPSP | Digital Workspace - Vigilance &amp; Anti-Corruption Bureau</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            color-scheme: dark;
            font-family: 'Inter', system-ui, sans-serif;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            --aurora-bg: #07152e;
            --aurora-fg: #f1f5ff;
            --aurora-muted: #a8b8d4;
            --aurora-step: #93c5fd;
            --aurora-accent: #3b82f6;
            --aurora-field: rgba(255, 255, 255, .07);
            --aurora-border: rgba(255, 255, 255, .16);
            --aurora-ring: rgba(59, 130, 246, .35);
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            color: var(--aurora-fg);
            background:
                radial-gradient(900px 500px at 12% 8%, rgba(37, 99, 235, .25), transparent 60%),
                radial-gradient(800px 600px at 95% 90%, rgba(14, 165, 233, .2), transparent 60%),
                var(--aurora-bg);
            font-family: 'Inter', system-ui, sans-serif;
        }

        .aurora-page {
            position: relative;
            isolation: isolate;
            min-height: 100vh;
        }

        .aurora-page::before {
            position: fixed;
            z-index: -1;
            inset: 0;
            pointer-events: none;
            background-image: radial-gradient(rgba(255, 255, 255, .07) 1px, transparent 1px);
            background-size: 26px 26px;
            -webkit-mask-image: radial-gradient(circle at 50% 50%, #000 30%, transparent 80%);
            mask-image: radial-gradient(circle at 50% 50%, #000 30%, transparent 80%);
            content: "";
        }

        .aurora-stage {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(480px, 540px);
            align-items: center;
            justify-content: center;
            gap: clamp(48px, 6vw, 100px);
            width: min(100%, 1600px);
            min-height: 100vh;
            margin: 0 auto;
            padding: clamp(40px, 5vw, 80px) clamp(48px, 6vw, 96px);
        }

        .aurora-story {
            max-width: 680px;
            animation: aurora-enter .8s cubic-bezier(.2, .7, .2, 1) both;
        }

        .aurora-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 44px;
        }

        .aurora-logo {
            display: grid;
            flex: none;
            width: 56px;
            height: 56px;
            place-items: center;
            border-radius: 14px;
            color: white;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            box-shadow: 0 8px 22px -8px rgba(59, 130, 246, .8);
            transition: box-shadow .25s ease, transform .25s ease;
        }

        .aurora-logo:hover {
            transform: translateY(-3px) rotate(-4deg);
            box-shadow: 0 14px 28px -8px rgba(59, 130, 246, .95);
        }

        .aurora-logo svg {
            width: 28px;
            height: 28px;
            animation: aurora-icon-float 4s ease-in-out 1s infinite;
        }

        .aurora-brand-name {
            color: #e2e8f0;
            font-family: 'Fraunces', Georgia, serif;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: .06em;
            line-height: 1;
        }

        .aurora-brand-org {
            margin-top: 6px;
            color: #a8b8d4;
            font-size: 15px;
            font-weight: 500;
        }

        .aurora-brand-sub {
            margin-top: 2px;
            color: var(--aurora-step);
            font-size: 13px;
            letter-spacing: .02em;
        }

        .aurora-headline {
            max-width: none;
            margin: 0;
            font-family: 'Fraunces', Georgia, serif;
            font-size: clamp(42px, 5vw, 68px);
            font-weight: 700;
            letter-spacing: -.02em;
            line-height: 1.04;
        }

        .aurora-headline em {
            color: var(--aurora-step);
            font-style: normal;
            white-space: nowrap;
        }

        .aurora-lead {
            max-width: 42ch;
            margin: 18px 0 0;
            color: #a8b8d4;
            font-size: 18px;
            line-height: 1.6;
        }

        .aurora-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            width: 100%;
            max-width: 460px;
            margin: 48px 0 0;
            padding: 0;
            list-style: none;
        }

        .aurora-step {
            position: relative;
            z-index: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 11px;
            color: #a8b8d4;
            font-size: 14px;
            font-weight: 600;
        }

        .aurora-step:not(:last-child)::after {
            position: absolute;
            z-index: -1;
            top: 24px;
            left: calc(50% + 28px);
            width: calc(100% - 56px);
            height: 1px;
            background: rgba(255, 255, 255, .18);
            content: "";
        }

        .aurora-step-icon {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 50%;
            background: transparent;
            color: #a8b8d4;
            transition: border-color .25s ease, background-color .25s ease, color .25s ease, box-shadow .25s ease, transform .25s ease;
        }

        .aurora-step:hover .aurora-step-icon {
            transform: translateY(-4px);
            border-color: var(--aurora-step);
            background: rgba(147, 197, 253, .1);
            color: var(--aurora-step);
            box-shadow: 0 0 0 5px var(--aurora-ring);
        }

        .aurora-step-icon svg {
            width: 22px;
            height: 22px;
        }

        .aurora-step:first-child {
            color: var(--aurora-fg);
        }

        .aurora-step:first-child .aurora-step-icon {
            border-color: var(--aurora-step);
            color: var(--aurora-step);
            box-shadow: 0 0 0 5px var(--aurora-ring);
        }

        .aurora-trust {
            display: flex;
            flex-wrap: wrap;
            gap: 26px;
            margin-top: 46px;
            color: #a8b8d4;
            font-size: 15px;
        }

        .aurora-trust span {
            display: flex;
            align-items: center;
            gap: 7px;
            transition: color .2s ease, transform .2s ease;
        }

        .aurora-trust span:hover {
            transform: translateY(-2px);
            color: var(--aurora-fg);
        }

        .aurora-trust svg {
            width: 18px;
            height: 18px;
            color: var(--aurora-step);
        }

        .aurora-formside {
            display: grid;
            place-items: center;
        }

        .aurora-card {
            width: 100%;
            max-width: 540px;
            padding: 48px 42px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 24px;
            background: rgba(255, 255, 255, .06);
            box-shadow: 0 30px 80px -30px rgba(0, 0, 0, .6), inset 0 1px 0 rgba(255, 255, 255, .12);
            -webkit-backdrop-filter: blur(18px);
            backdrop-filter: blur(18px);
            animation: aurora-enter .8s cubic-bezier(.2, .7, .2, 1) .12s both;
            transition: border-color .3s ease, box-shadow .3s ease;
        }

        .aurora-card:hover {
            border-color: rgba(147, 197, 253, .3);
            box-shadow: 0 34px 90px -30px rgba(0, 0, 0, .68), inset 0 1px 0 rgba(255, 255, 255, .14);
        }

        .aurora-eyebrow {
            color: var(--aurora-step);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .aurora-card h2 {
            margin: 8px 0 0;
            font-family: 'Fraunces', Georgia, serif;
            font-size: 38px;
            letter-spacing: -.01em;
            line-height: 1.1;
        }

        .aurora-card-sub {
            margin: 8px 0 0;
            color: var(--aurora-muted);
            font-size: 16px;
            line-height: 1.55;
        }

        .aurora-login-form {
            display: grid;
            gap: 22px;
            margin-top: 32px;
        }

        .aurora-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
        }

        .aurora-field {
            position: relative;
        }

        .aurora-field-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            width: 20px;
            height: 20px;
            transform: translateY(-50%);
            color: #9aacc9;
            pointer-events: none;
        }

        .aurora-card .aurora-field input,
        .aurora-field input {
            width: 100%;
            height: 58px;
            padding: 0 50px 0 48px;
            border: 1px solid var(--aurora-border);
            border-radius: 12px;
            outline: none;
            background: var(--aurora-field);
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff;
            font: 500 17px 'Inter', system-ui, sans-serif;
            transition: border-color .2s, box-shadow .2s;
        }

        .aurora-card .aurora-field input::placeholder,
        .aurora-field input::placeholder {
            color: rgba(255, 255, 255, 0.65) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.65) !important;
            font-weight: 400;
        }

        .aurora-field input:-webkit-autofill,
        .aurora-field input:-webkit-autofill:hover,
        .aurora-field input:-webkit-autofill:focus,
        .aurora-field input:-webkit-autofill:active {
            -webkit-text-fill-color: #ffffff !important;
            box-shadow: 0 0 0 1000px #07152e inset !important;
            -webkit-box-shadow: 0 0 0 1000px #07152e inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .aurora-field input:focus {
            border-color: var(--aurora-accent);
            box-shadow: 0 0 0 4px var(--aurora-ring);
        }

        .aurora-field:focus-within .aurora-field-icon {
            color: var(--aurora-step);
        }

        .aurora-field input[aria-invalid="true"] {
            border-color: #ff8a80;
        }

        .aurora-eye {
            position: absolute;
            top: 50%;
            right: 6px;
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #9aacc9;
            cursor: pointer;
            transition: color .2s ease, background-color .2s ease, transform .2s ease;
        }

        .aurora-eye:hover {
            transform: translateY(-50%) scale(1.06);
            background: rgba(127, 127, 127, .12);
            color: var(--aurora-fg);
        }

        .aurora-eye:focus-visible,
        .aurora-submit:focus-visible {
            outline: 3px solid var(--aurora-step);
            outline-offset: 3px;
        }

        .aurora-eye svg {
            width: 20px;
            height: 20px;
        }

        .aurora-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            color: #ffd37a;
            font-size: 14px;
        }

        .aurora-hint svg {
            width: 16px;
            height: 16px;
        }

        .aurora-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid rgba(255, 138, 128, .3);
            border-radius: 12px;
            background: rgba(255, 90, 80, .14);
            color: #ffb4ab;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.45;
        }

        .aurora-error svg {
            flex: none;
            width: 20px;
            height: 20px;
            margin-top: 1px;
        }

        .aurora-status {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 20px;
            padding: 12px 14px;
            border: 1px solid rgba(147, 197, 253, .25);
            border-radius: 12px;
            background: rgba(59, 130, 246, .12);
            color: var(--aurora-step);
            font-size: 15px;
            font-weight: 500;
            line-height: 1.45;
        }

        .aurora-status svg {
            flex: none;
            width: 20px;
            height: 20px;
        }

        .aurora-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
        }

        .aurora-check {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--aurora-muted);
            cursor: pointer;
            user-select: none;
        }

        .aurora-check input {
            display: grid;
            width: 18px;
            height: 18px;
            margin: 0;
            appearance: none;
            place-items: center;
            border: 1.5px solid var(--aurora-border);
            border-radius: 5px;
            background: var(--aurora-field);
            cursor: pointer;
        }

        .aurora-check input:checked {
            border-color: var(--aurora-accent);
            background: var(--aurora-accent);
        }

        .aurora-check input:checked::after {
            width: 9px;
            height: 5px;
            transform: rotate(-45deg) translate(1px, -1px);
            border-bottom: 2px solid white;
            border-left: 2px solid white;
            content: "";
        }

        .aurora-check input:focus-visible {
            outline: 3px solid var(--aurora-ring);
            outline-offset: 2px;
        }

        .aurora-forgot {
            color: var(--aurora-step);
            font-weight: 600;
            text-decoration: none;
        }

        .aurora-forgot:hover {
            text-decoration: underline;
        }

        .aurora-submit {
            display: flex;
            height: 60px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(180deg, #60a5fa, #2563eb);
            box-shadow: 0 12px 24px -12px rgba(37, 99, 235, .85), inset 0 1px 0 rgba(255, 255, 255, .2);
            color: #ffffff;
            cursor: pointer;
            font: 600 17px 'Inter', system-ui, sans-serif;
            transition: filter .2s, transform .2s, box-shadow .2s;
        }

        .aurora-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            filter: brightness(1.06);
            box-shadow: 0 16px 30px -12px rgba(37, 99, 235, .95), inset 0 1px 0 rgba(255, 255, 255, .25);
        }

        .aurora-submit:active:not(:disabled) {
            transform: translateY(1px);
        }

        .aurora-submit:disabled {
            cursor: progress;
            opacity: .8;
        }

        .aurora-submit svg {
            width: 20px;
            height: 20px;
        }

        .aurora-spinner {
            width: 20px;
            height: 20px;
            animation: aurora-spin .7s linear infinite;
            border: 2.5px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
        }

        @keyframes aurora-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes aurora-enter {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes aurora-icon-float {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        .aurora-legal {
            display: flex;
            gap: 9px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, .12);
            color: var(--aurora-muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .aurora-legal svg {
            flex: none;
            width: 18px;
            height: 18px;
            margin-top: 2px;
            color: var(--aurora-step);
        }

        .aurora-footer {
            padding: 0 24px 24px;
            color: rgba(168, 184, 212, .8);
            font-size: 14px;
            text-align: center;
        }

        .aurora-footer strong {
            color: #cbd5e1;
        }

        @media (max-width: 960px) {
            .aurora-stage {
                grid-template-columns: minmax(0, 1fr);
                gap: 34px;
                padding: 40px 18px;
            }

            .aurora-story {
                width: min(100%, 680px);
                margin: 0 auto;
            }

            .aurora-brand {
                margin-bottom: 28px;
            }

            .aurora-headline {
                font-size: 42px;
            }

            .aurora-steps {
                margin-top: 28px;
            }

            .aurora-trust {
                margin-top: 28px;
            }
        }

        @media (max-width: 480px) {
            .aurora-stage {
                gap: 28px;
                padding: 28px 16px;
            }

            .aurora-brand-org {
                font-size: 12px;
            }

            .aurora-brand-sub {
                font-size: 11px;
            }

            .aurora-brand-name {
                font-size: 27px;
            }

            .aurora-headline {
                font-size: 36px;
            }

            .aurora-headline em {
                font-size: clamp(24px, 7.5vw, 36px);
            }

            .aurora-card {
                padding: 34px 24px;
                border-radius: 20px;
            }

            .aurora-row {
                font-size: 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="aurora-layers" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 2 10 5-10 5L2 7l10-5Z" />
            <path d="m2 17 10 5 10-5M2 12l10 5 10-5" />
        </symbol>
        <symbol id="aurora-file" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
            <path d="M14 2v6h6M12 18v-6m-3 3h6" />
        </symbol>
        <symbol id="aurora-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7" />
            <path d="m21 21-4.3-4.3M11 8v6m-3-3h6" />
        </symbol>
        <symbol id="aurora-send" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="m22 2-7 20-4-9-9-4Z" />
            <path d="M22 2 11 13" />
        </symbol>
        <symbol id="aurora-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="m8 12 3 3 5-6" />
        </symbol>
        <symbol id="aurora-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="m9 12 2 2 4-4" />
        </symbol>
        <symbol id="aurora-id" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2.5" />
            <circle cx="8.5" cy="11" r="2" />
            <path d="M5.5 16c.6-1.6 1.8-2.4 3-2.4s2.4.8 3 2.4M14.5 10h4m-4 4h3" />
        </symbol>
        <symbol id="aurora-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="4" y="11" width="16" height="10" rx="2.5" />
            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
        </symbol>
        <symbol id="aurora-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
            <circle cx="12" cy="12" r="3" />
        </symbol>
        <symbol id="aurora-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.7 5.1A10 10 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 5.4-1.6" />
            <path d="m9.9 9.9 4.2 4.2M2 2l20 20" />
        </symbol>
        <symbol id="aurora-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14m-6-6 6 6-6 6" />
        </symbol>
        <symbol id="aurora-alert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 8v5m0 3.5h.01" />
        </symbol>
        <symbol id="aurora-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 16v-4m0-4h.01" />
        </symbol>
    </svg>

    <div class="aurora-page" x-data="{ showPassword: false, loading: false, capsLock: false }">
        <main class="aurora-stage">
            <section class="aurora-story" aria-label="About CPSP">
                <div class="aurora-brand">
                    <div class="aurora-logo" aria-hidden="true">
                        <svg><use href="#aurora-layers" /></svg>
                    </div>
                    <div>
                        <div class="aurora-brand-name">CPSP</div>
                        <div class="aurora-brand-org">Vigilance &amp; Anti-Corruption Bureau</div>
                        <div class="aurora-brand-sub">Centralized Processing System of Petitions Cell</div>
                    </div>
                </div>

                <h1 class="aurora-headline">Process petitions. <em>Track every petition.</em></h1>
                <p class="aurora-lead">
                    Monitor updates across petitions and generate reports — all in one secure workspace.
                </p>

                <ol class="aurora-steps" aria-label="Petition lifecycle">
                    <li class="aurora-step">
                        <span class="aurora-step-icon"><svg><use href="#aurora-file" /></svg></span>
                        <span>Register</span>
                    </li>
                    <li class="aurora-step">
                        <span class="aurora-step-icon"><svg><use href="#aurora-search" /></svg></span>
                        <span>Review</span>
                    </li>
                    <li class="aurora-step">
                        <span class="aurora-step-icon"><svg><use href="#aurora-send" /></svg></span>
                        <span>Forward</span>
                    </li>
                    <li class="aurora-step">
                        <span class="aurora-step-icon"><svg><use href="#aurora-check" /></svg></span>
                        <span>Resolve</span>
                    </li>
                </ol>

                <div class="aurora-trust" aria-label="Security commitments">
                    <span><svg><use href="#aurora-shield" /></svg>Secure</span>
                    <span><svg><use href="#aurora-shield" /></svg>Reliable</span>
                    <span><svg><use href="#aurora-shield" /></svg>Accountable</span>
                </div>
            </section>

            <section class="aurora-formside" aria-label="Sign in">
                <div class="aurora-card">
                    <div class="aurora-eyebrow">Officer sign in</div>
                    <h2>Welcome back</h2>
                    <p class="aurora-card-sub">Use your PEN and password to open your workspace.</p>

                    @if (session('status'))
                        <div class="aurora-status" role="status">
                            <svg><use href="#aurora-check" /></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any() && !$errors->has('pen') && !$errors->has('password'))
                        <div class="aurora-error" role="alert" style="margin-top: 20px;">
                            <svg><use href="#aurora-alert" /></svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" @submit="loading = true" class="aurora-login-form">
                        @csrf

                        <div>
                            <label class="aurora-label" for="pen">PEN Number</label>
                            <div class="aurora-field">
                                <svg class="aurora-field-icon" aria-hidden="true"><use href="#aurora-id" /></svg>
                                <input id="pen" name="pen" type="text" inputmode="numeric" autocomplete="username"
                                    placeholder="Enter your PEN" value="{{ old('pen') }}" required autofocus
                                    aria-invalid="{{ $errors->has('pen') ? 'true' : 'false' }}"
                                    @if ($errors->has('pen')) aria-describedby="pen-error" @endif>
                            </div>
                            @error('pen')
                                <p class="aurora-error" id="pen-error" role="alert" style="margin-top: 8px;">
                                    <svg><use href="#aurora-alert" /></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="aurora-label" for="password">Password</label>
                            <div class="aurora-field">
                                <svg class="aurora-field-icon" aria-hidden="true"><use href="#aurora-lock" /></svg>
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password" placeholder="Enter your password" required
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                    @if ($errors->has('password')) aria-describedby="password-error" @endif
                                    @keydown="capsLock = $event.getModifierState && $event.getModifierState('CapsLock')"
                                    @keyup="capsLock = $event.getModifierState && $event.getModifierState('CapsLock')"
                                    @blur="capsLock = false">
                                <button type="button" class="aurora-eye" @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    :aria-pressed="showPassword.toString()">
                                    <svg x-show="!showPassword"><use href="#aurora-eye" /></svg>
                                    <svg x-show="showPassword" x-cloak><use href="#aurora-eye-off" /></svg>
                                </button>
                            </div>
                            <p class="aurora-hint" x-show="capsLock" x-cloak>
                                <svg><use href="#aurora-info" /></svg>
                                Caps Lock is on
                            </p>
                            @error('password')
                                <p class="aurora-error" id="password-error" role="alert" style="margin-top: 8px;">
                                    <svg><use href="#aurora-alert" /></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <div class="aurora-row">
                            <label class="aurora-check" for="remember">
                                <input type="checkbox" name="remember" id="remember"
                                    {{ old('remember', true) ? 'checked' : '' }}>
                                <span>Remember me</span>
                            </label>

                            {{-- @if (Route::has('password.request'))
                                <a class="aurora-forgot" href="{{ route('password.request') }}">Forgot password?</a>
                            @endif --}}
                        </div>

                        <button class="aurora-submit" type="submit" :disabled="loading">
                            <span x-show="!loading" class="inline-flex items-center gap-2">
                                <span>Sign in</span>
                                <svg><use href="#aurora-arrow" /></svg>
                            </span>
                            <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                <span class="aurora-spinner" aria-hidden="true"></span>
                                <span>Signing in...</span>
                            </span>
                        </button>
                    </form>

                    <div class="aurora-legal">
                        <svg><use href="#aurora-shield" /></svg>
                        <span>Authorised personnel only. All activity in this system is logged and monitored.</span>
                    </div>
                </div>
            </section>
        </main>

        <footer class="aurora-footer">
            <p>Designed by <strong>Software Development Division</strong><br>VACB Directorate, Kerala.</p>
        </footer>
    </div>
</body>

</html>
