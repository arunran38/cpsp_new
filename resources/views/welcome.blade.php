<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Centralized Processing System of Petitions - Official Portal of Vigilance & Anti-Corruption Bureau, Government of Kerala">
    <title>CPSP | Digital Workspace - Vigilance & Anti-Corruption Bureau</title>

    <!-- Google Fonts: Inter, Playfair Display (Serif) & Noto Sans Malayalam -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&family=Noto+Sans+Malayalam:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #060d19;
            color: #f1f5f9;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .font-serif-display {
            font-family: 'Playfair Display', Georgia, serif;
        }

        .font-malayalam {
            font-family: 'Noto Sans Malayalam', 'Inter', sans-serif;
        }

        /* Ambient deep navy background with radial emerald/teal glow */
        .page-bg {
            background-color: #060d19;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(13, 148, 136, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 45% 65%, rgba(16, 185, 129, 0.10) 0%, transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(14, 165, 233, 0.08) 0%, transparent 50%),
                linear-gradient(180deg, #040812 0%, #060d19 50%, #050b16 100%);
            position: relative;
        }

        /* Ambient decorative circuit line container */
        .ambient-circuit-svg {
            position: absolute;
            inset: 0;
            pointer-events: none;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Glowing circuit line */
        .glowing-circuit-path {
            stroke: #10b981;
            stroke-width: 1.75;
            fill: none;
            filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.7));
            opacity: 0.85;
        }

        .circuit-branch {
            stroke: rgba(20, 184, 166, 0.35);
            stroke-width: 1.2;
            fill: none;
        }

        /* 3D Isometric Workflow Cards Container */
        .isometric-stage {
            perspective: 1200px;
            transform-style: preserve-3d;
        }

        .isometric-cards-wrap {
            transform: rotateX(56deg) rotateY(0deg) rotateZ(-36deg);
            transform-style: preserve-3d;
            position: relative;
            width: 280px;
            height: 180px;
        }

        .iso-card {
            position: absolute;
            border-radius: 1.25rem;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .iso-card-1 {
            width: 130px;
            height: 155px;
            left: 0;
            top: 25px;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.75) 0%, rgba(15, 23, 42, 0.85) 100%);
            border: 1px solid rgba(96, 165, 250, 0.4);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            z-index: 4;
        }

        .iso-card-2 {
            width: 125px;
            height: 150px;
            left: 55px;
            top: 15px;
            background: linear-gradient(135deg, rgba(13, 148, 136, 0.6) 0%, rgba(15, 118, 110, 0.75) 100%);
            border: 1px solid rgba(45, 212, 191, 0.4);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            z-index: 3;
        }

        .iso-card-3 {
            width: 120px;
            height: 145px;
            left: 110px;
            top: 5px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: 1px solid rgba(110, 231, 183, 0.6);
            box-shadow: 0 20px 40px -5px rgba(16, 185, 129, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.4);
            z-index: 2;
        }

        .iso-card-4 {
            width: 115px;
            height: 140px;
            left: 165px;
            top: -5px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(13, 148, 136, 0.4) 100%);
            border: 1px solid rgba(20, 184, 166, 0.3);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.15);
            z-index: 1;
        }

        /* Workflow Step Node Badges */
        .workflow-node-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.375rem;
            position: relative;
            z-index: 10;
        }

        .workflow-node-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            background: rgba(10, 22, 40, 0.85);
            border: 1px solid rgba(45, 212, 191, 0.3);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2dd4bf;
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.5), 0 0 12px rgba(20, 184, 166, 0.15);
            transition: all 0.25s ease;
        }

        .workflow-node-pill:hover .workflow-node-icon {
            transform: translateY(-2px);
            border-color: #34d399;
            color: #34d399;
            box-shadow: 0 8px 20px -2px rgba(16, 185, 129, 0.35);
        }

        .workflow-node-pill.active-node .workflow-node-icon {
            background: #10b981;
            border-color: #6ee7b7;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.6);
        }

        /* Glowing Node Dots on Circuit Line */
        .circuit-node-dot {
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 50%;
            background-color: #34d399;
            box-shadow: 0 0 10px #10b981, 0 0 18px rgba(52, 211, 153, 0.8);
            position: absolute;
            z-index: 5;
        }

        /* ========================================================= */
        /* NEUMORPHIC FLOATING CARD (Requested by user)              */
        /* ========================================================= */
        :root {
            --neu-card-bg: #f5f6f8;
            --neu-input-bg: #eceef1;
            --neu-shadow-dark: #cbced4;
            --neu-shadow-light: #ffffff;
            --primary-emerald: #0d9488;
        }

        .neu-login-card {
            background-color: var(--neu-card-bg);
            border-radius: 2rem;
            padding: 2.5rem 2.25rem;
            box-shadow: 
                0 25px 60px -12px rgba(0, 0, 0, 0.75),
                0 10px 25px -5px rgba(0, 0, 0, 0.4),
                inset 1px 1px 2px rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        /* Inset Neumorphic Input Fields (Exact texture and design of security callout) */
        .neu-input-container {
            background-color: #eceef2;
            border-radius: 0.875rem;
            box-shadow: 
                inset 2px 2px 4px var(--neu-shadow-dark), 
                inset -2px -2px 4px var(--neu-shadow-light);
            padding: 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            height: 3rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .neu-input-container:focus-within {
            box-shadow: 
                inset 2px 2px 4px var(--neu-shadow-dark), 
                inset -2px -2px 4px var(--neu-shadow-light);
            border-color: rgba(13, 148, 136, 0.45);
        }

        .neu-input-container.has-error {
            box-shadow: 
                inset 2px 2px 4px #fca5a5, 
                inset -2px -2px 4px var(--neu-shadow-light);
            border-color: #f87171;
        }

        .neu-input-container .input-icon-left {
            color: #3b82f6;
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        .neu-form-input,
        .neu-input-container input[type="text"],
        .neu-input-container input[type="password"] {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            height: 100% !important;
            background: transparent !important;
            border: 0 !important;
            border-width: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 0.9375rem !important;
            font-family: inherit !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            -webkit-appearance: none !important;
            appearance: none !important;
            --tw-ring-color: transparent !important;
            --tw-ring-shadow: none !important;
            --tw-ring-offset-shadow: none !important;
        }

        .neu-form-input:focus,
        .neu-input-container input[type="text"]:focus,
        .neu-input-container input[type="password"]:focus {
            border: 0 !important;
            border-width: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            --tw-ring-color: transparent !important;
            --tw-ring-shadow: none !important;
            --tw-ring-offset-shadow: none !important;
        }

        .neu-form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Neumorphic / Emerald Gradient Submit Button */
        .btn-emerald-submit {
            width: 100%;
            height: 3.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, #0d9488 0%, #059669 100%);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            box-shadow: 0 10px 22px -4px rgba(13, 148, 136, 0.45), 0 4px 8px -2px rgba(5, 150, 105, 0.3);
            transition: all 0.2s ease;
            outline: none;
            text-decoration: none;
        }

        .btn-emerald-submit:hover:not(:disabled) {
            background: linear-gradient(135deg, #0f766e 0%, #047857 100%);
            box-shadow: 0 14px 28px -4px rgba(13, 148, 136, 0.55), 0 6px 12px -2px rgba(5, 150, 105, 0.4);
            transform: translateY(-1px);
        }

        .btn-emerald-submit:active:not(:disabled) {
            transform: translateY(1px);
            box-shadow: 0 4px 10px -2px rgba(13, 148, 136, 0.4);
        }

        .btn-emerald-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Subtle Security Notice Box at Bottom of Card */
        .neu-security-callout {
            background-color: #eceef2;
            border-radius: 0.875rem;
            box-shadow: 
                inset 2px 2px 4px var(--neu-shadow-dark), 
                inset -2px -2px 4px var(--neu-shadow-light);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.35;
        }

        /* Top Bar Pill Controls */
        .top-ctrl-pill {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
            padding: 0.25rem 0.375rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>
</head>

<body class="h-full page-bg" x-data="{ 
    lang: 'en', 
    darkMode: true, 
    showPassword: false, 
    loading: false 
}">

    <!-- Full Screen Viewport Wrapper -->
    <div class="min-h-screen flex flex-col justify-between relative z-10">

        <!-- ========================================================= -->
        <!-- TOP NAVBAR: Brand Logo & Language / Dark Theme Toggles     -->
        <!-- ========================================================= -->
        <header class="w-full px-6 sm:px-10 lg:px-14 pt-6 pb-2 flex items-center justify-between z-20">
            <!-- Brand Mark: Layered Emerald Icon + Name -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center">
                    <img src="{{ asset('images/finished_metal_transparent.png') }}" 
                         alt="Kerala VACB Emblem" 
                         class="w-full h-full object-contain filter drop-shadow-[0_4px_8px_rgba(0,0,0,0.5)]"
                         onerror="this.src='{{ asset('images/logo.png') }}';">
                </div>
                <div class="leading-none">
                    <div class="flex items-center gap-1.5">
                        <span class="text-lg font-extrabold tracking-tight text-white font-serif-display">CPSP</span>
                        <span class="text-[11px] px-1.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-semibold uppercase tracking-wider">VACB</span>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium tracking-wide mt-0.5">
                        Centralized Processing System of Petitions
                    </div>
                </div>
            </div>

            <!-- Top Right Controls: Theme Toggle & Language Selector -->
            <div class="flex items-center gap-4">
                <!-- Theme Switcher Pill [ ☀️ | 🌙 ] -->
                <div class="top-ctrl-pill">
                    <button type="button" 
                            @click="darkMode = false" 
                            :class="!darkMode ? 'bg-white/20 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                            class="p-1.5 rounded-full transition-colors" 
                            aria-label="Light mode">
                        <i data-lucide="sun" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" 
                            @click="darkMode = true" 
                            :class="darkMode ? 'bg-white/20 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                            class="p-1.5 rounded-full transition-colors" 
                            aria-label="Dark mode">
                        <i data-lucide="moon" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Language Selector: EN | മലയാളം -->
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <button type="button" 
                            @click="lang = 'en'"
                            :class="lang === 'en' ? 'text-white border-b-2 border-emerald-400 pb-0.5' : 'text-slate-400 hover:text-slate-200'"
                            class="transition-colors uppercase tracking-wider">
                        EN
                    </button>
                    <span class="text-slate-600">|</span>
                    <button type="button" 
                            @click="lang = 'ml'"
                            :class="lang === 'ml' ? 'text-white border-b-2 border-emerald-400 pb-0.5' : 'text-slate-400 hover:text-slate-200'"
                            class="transition-colors font-malayalam">
                        മലയാളം
                    </button>
                </div>
            </div>
        </header>

        <!-- ========================================================= -->
        <!-- MAIN CONTENT: 2-Column Split (Hero Graphic & Login Card)  -->
        <!-- ========================================================= -->
        <main class="w-full flex-1 max-w-[1400px] mx-auto px-6 sm:px-10 lg:px-14 py-6 lg:py-10 flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-16">

            <!-- LEFT HERO SECTION: Headline, 3D Isometric Visual & Steps -->
            <div class="w-full lg:w-7/12 flex flex-col justify-between relative z-10">

                <!-- Editorial Headline (Matching Reference Typography) -->
                <div class="max-w-xl">
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-bold text-white tracking-tight leading-[1.12] font-serif-display">
                        <span x-show="lang === 'en'">A Smarter<br>Way to Process</span>
                        <span x-show="lang === 'ml'" x-cloak class="font-malayalam leading-[1.25]">കാര്യക്ഷമമായ<br>ഹർജി പരിഹാരം</span>
                    </h1>
                    <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed max-w-lg">
                        <span x-show="lang === 'en'">
                            Register, verify, scrutinize and resolve public petitions with jurisdictional precision — all in one secure government digital workspace.
                        </span>
                        <span x-show="lang === 'ml'" x-cloak class="font-malayalam">
                            ഹർജികൾ രജിസ്റ്റർ ചെയ്യാനും, സീറ്റ് അടിസ്ഥാനത്തിൽ പരിശോധിക്കാനും, വേഗത്തിൽ തീർപ്പാക്കാനും — ഔദ്യോഗിക ഡിജിറ്റൽ സംവിധാനം.
                        </span>
                    </p>
                </div>

                <!-- Central 3D Isometric Workflow Cards & Connected Nodes Graphic -->
                <div class="my-8 lg:my-12 relative w-full max-w-2xl">
                    
                    <!-- SVG Circuit Pathway Background -->
                    <svg class="ambient-circuit-svg" viewBox="0 0 680 260" fill="none">
                        <!-- Curved glowing data flow lines -->
                        <path d="M 30,195 Q 120,195 160,175 T 300,120 T 450,140 T 630,195" class="glowing-circuit-path" />
                        <path d="M 20,145 Q 80,145 140,110 T 280,75 T 480,95 T 650,135" class="circuit-branch" />
                        <path d="M 270,75 L 300,120" class="circuit-branch" stroke-dasharray="3 3" />
                        <path d="M 450,140 L 480,95" class="circuit-branch" stroke-dasharray="3 3" />
                    </svg>

                    <!-- Glowing Dots on Circuit Line -->
                    <div class="circuit-node-dot" style="left: 14%; top: 68%;"></div>
                    <div class="circuit-node-dot" style="left: 42%; top: 43%;"></div>
                    <div class="circuit-node-dot" style="left: 65%; top: 51%;"></div>
                    <div class="circuit-node-dot" style="left: 92%; top: 68%;"></div>

                    <!-- 3D Floating Layered Glass Cards (Perspective Visual) -->
                    <div class="flex justify-center mb-6 pl-12 sm:pl-20">
                        <div class="isometric-stage">
                            <div class="isometric-cards-wrap">
                                <!-- Card 1: Slate Blue -->
                                <div class="iso-card iso-card-1 p-3">
                                    <div class="w-6 h-1.5 rounded-full bg-blue-300/40 mb-2"></div>
                                    <div class="space-y-1.5 opacity-60">
                                        <div class="w-full h-1 bg-white/30 rounded"></div>
                                        <div class="w-3/4 h-1 bg-white/30 rounded"></div>
                                        <div class="w-1/2 h-1 bg-white/30 rounded"></div>
                                    </div>
                                </div>
                                <!-- Card 2: Cyan / Teal -->
                                <div class="iso-card iso-card-2 p-3">
                                    <div class="w-6 h-1.5 rounded-full bg-teal-300/50 mb-2"></div>
                                    <div class="space-y-1.5 opacity-70">
                                        <div class="w-full h-1 bg-white/40 rounded"></div>
                                        <div class="w-4/5 h-1 bg-white/40 rounded"></div>
                                        <div class="w-2/3 h-1 bg-white/40 rounded"></div>
                                    </div>
                                </div>
                                <!-- Card 3: Emerald Glow (Active Tile) -->
                                <div class="iso-card iso-card-3 p-3 flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <div class="w-7 h-1.5 rounded-full bg-white/70"></div>
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-full h-1 bg-white/60 rounded"></div>
                                        <div class="w-5/6 h-1 bg-white/60 rounded"></div>
                                    </div>
                                </div>
                                <!-- Card 4: Deep Teal -->
                                <div class="iso-card iso-card-4 p-3">
                                    <div class="w-6 h-1.5 rounded-full bg-teal-200/40 mb-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5 Connected Workflow Step Nodes (Matching Reference) -->
                    <div class="grid grid-cols-5 gap-2 sm:gap-4 pt-4 relative z-10 text-center">
                        <!-- Step 1: Create / Inward -->
                        <div class="workflow-node-pill">
                            <div class="workflow-node-icon">
                                <i data-lucide="file-plus-2" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-300">
                                <span x-show="lang === 'en'">Create</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam text-[11px]">ഇൻവേർഡ്</span>
                            </span>
                        </div>

                        <!-- Step 2: Review / Scrutiny -->
                        <div class="workflow-node-pill">
                            <div class="workflow-node-icon">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-300">
                                <span x-show="lang === 'en'">Review</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam text-[11px]">പരിശോധന</span>
                            </span>
                        </div>

                        <!-- Step 3: Collaborate / Seat Routing -->
                        <div class="workflow-node-pill">
                            <div class="workflow-node-icon">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-300">
                                <span x-show="lang === 'en'">Assign</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam text-[11px]">സീറ്റ് വിതരണം</span>
                            </span>
                        </div>

                        <!-- Step 4: Approve / Order (Active Node) -->
                        <div class="workflow-node-pill active-node">
                            <div class="workflow-node-icon">
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-emerald-400">
                                <span x-show="lang === 'en'">Approve</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam text-[11px]">ഉത്തരവ്</span>
                            </span>
                        </div>

                        <!-- Step 5: Archive / Disposal -->
                        <div class="workflow-node-pill">
                            <div class="workflow-node-icon">
                                <i data-lucide="archive" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-300">
                                <span x-show="lang === 'en'">Archive</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam text-[11px]">തീർപ്പാക്കൽ</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Trust Badge -->
                <div class="flex items-center gap-2.5 text-xs text-slate-400 font-medium pt-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                    <span>Secure</span>
                    <span class="text-slate-600">•</span>
                    <span>Reliable</span>
                    <span class="text-slate-600">•</span>
                    <span>Audited Government Network</span>
                </div>
            </div>

            <!-- RIGHT SECTION: Neumorphic Floating Card (Matching Reference Specs) -->
            <div class="w-full lg:w-5/12 max-w-[430px] mx-auto z-10">
                <div class="neu-login-card">

                    <!-- Top Card Logo -->
                    <div class="flex items-center justify-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-md shadow-teal-500/30">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <div class="leading-none text-left">
                            <div class="text-base font-bold text-slate-900 tracking-tight">CPSP Portal</div>
                            <div class="text-[11px] text-slate-500 font-medium">Digital Workspace</div>
                        </div>
                    </div>

                    <!-- Welcome Back Heading -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl sm:text-[1.75rem] font-bold text-slate-900 tracking-tight font-serif-display leading-tight">
                            <span x-show="lang === 'en'">Welcome Back</span>
                            <span x-show="lang === 'ml'" x-cloak class="font-malayalam">സ്വാഗതം</span>
                        </h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
                            <span x-show="lang === 'en'">Enter your credentials to continue to your digital workspace.</span>
                            <span x-show="lang === 'ml'" x-cloak class="font-malayalam">നിങ്ങളുടെ യൂസർ വിവരങ്ങൾ നൽകി ലോഗിൻ ചെയ്യുക.</span>
                        </p>
                    </div>

                    <!-- Session Status or Global Alerts -->
                    @if (session('status'))
                        <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center gap-2" role="status">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" 
                          action="{{ route('login') }}" 
                          @submit="loading = true"
                          class="space-y-4"
                          novalidate>
                        @csrf

                        <!-- PEN (Permanent Employee Number) Field -->
                        <div>
                            <label for="pen" class="block text-xs font-semibold text-slate-700 mb-1.5 ml-0.5">
                                <span x-show="lang === 'en'">PEN Number</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam">പി.ഇ.എൻ നമ്പർ (PEN)</span>
                            </label>
                            <div class="neu-input-container {{ $errors->has('pen') ? 'has-error' : '' }}">
                                <div class="input-icon-left">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <input type="text" 
                                       inputmode="numeric"
                                       name="pen" 
                                       id="pen" 
                                       value="{{ old('pen') }}" 
                                       placeholder="you@example.com / PEN" 
                                       required 
                                       autofocus 
                                       autocomplete="username"
                                       aria-label="PEN Number"
                                       aria-invalid="{{ $errors->has('pen') ? 'true' : 'false' }}"
                                       @if ($errors->has('pen')) aria-describedby="pen-error" @endif
                                       class="neu-form-input">
                            </div>
                            @error('pen')
                                <p class="mt-1.5 text-xs font-medium text-rose-600 px-1" id="pen-error" role="alert">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password Field with Accessible Show/Hide Toggle -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5 ml-0.5">
                                <span x-show="lang === 'en'">Password</span>
                                <span x-show="lang === 'ml'" x-cloak class="font-malayalam">പാസ്‌വേഡ്</span>
                            </label>
                            <div class="neu-input-container {{ $errors->has('password') ? 'has-error' : '' }}">
                                <div class="input-icon-left">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" 
                                       name="password" 
                                       id="password" 
                                       placeholder="Enter your password" 
                                       required 
                                       autocomplete="current-password"
                                       aria-label="Password"
                                       aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                       @if ($errors->has('password')) aria-describedby="password-error" @endif
                                       class="neu-form-input">
                                
                                <button type="button" 
                                        @click="showPassword = !showPassword" 
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                        tabindex="-1"
                                        class="text-blue-500 hover:text-blue-700 focus:outline-none transition-colors flex items-center flex-shrink-0">
                                    <span x-show="!showPassword">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </span>
                                    <span x-show="showPassword" x-cloak>
                                        <i data-lucide="eye-off" class="w-4 h-4"></i>
                                    </span>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs font-medium text-rose-600 px-1" id="password-error" role="alert">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password Row -->
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none group">
                                <input type="checkbox" 
                                       name="remember" 
                                       id="remember" 
                                       {{ old('remember', true) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 cursor-pointer">
                                <span class="text-xs text-slate-600 group-hover:text-slate-900 transition-colors">
                                    <span x-show="lang === 'en'">Remember me</span>
                                    <span x-show="lang === 'ml'" x-cloak class="font-malayalam">ഓർത്തു വെക്കുക</span>
                                </span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" 
                                   class="text-xs font-semibold text-teal-700 hover:text-teal-800 hover:underline transition-colors">
                                    <span x-show="lang === 'en'">Forgot password?</span>
                                    <span x-show="lang === 'ml'" x-cloak class="font-malayalam">പാസ്‌വേഡ് മറന്നോ?</span>
                                </a>
                            @endif
                        </div>

                        <!-- Sign In Button (Vibrant Emerald Gradient Pill) -->
                        <div class="pt-2">
                            <button type="submit" 
                                    :disabled="loading" 
                                    class="btn-emerald-submit">
                                <span x-show="!loading" class="inline-flex items-center gap-2">
                                    <span x-show="lang === 'en'">Sign In</span>
                                    <span x-show="lang === 'ml'" x-cloak class="font-malayalam">പ്രവേശിക്കുക</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </span>
                                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                    <span>Authenticating...</span>
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Divider: —— or —— -->
                    <div class="relative my-5 text-center">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="bg-[#f5f6f8] px-3 text-slate-400 font-medium">or</span>
                        </div>
                    </div>

                    <!-- Bottom Security Disclaimer Box (Matching Reference) -->
                    <div class="neu-security-callout">
                        <div class="input-icon-left">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <span>
                            <span x-show="lang === 'en'">This is a secure government system. Please keep your credentials confidential.</span>
                            <span x-show="lang === 'ml'" x-cloak class="font-malayalam">ഇതൊരു സുരക്ഷിത സർക്കാർ പോർട്ടലാണ്. ലോഗിൻ വിവരങ്ങൾ രഹസ്യമായി സൂക്ഷിക്കുക.</span>
                        </span>
                    </div>

                </div>
            </div>
        </main>

        <!-- ========================================================= -->
        <!-- BOTTOM FOOTER: Official VACB Government Attribution      -->
        <!-- ========================================================= -->
        <footer class="w-full px-6 py-4 text-center text-xs text-slate-500 z-10 border-t border-white/5">
            <p>Designed & Developed by <span class="text-slate-300 font-semibold">Software Development Division</span> • Directorate of Vigilance & Anti-Corruption Bureau, Kerala.</p>
        </footer>

    </div>

    <!-- SweetAlert Session Notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.sweetalert-session')

    <!-- Lucide Icon Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
