<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Centralized Processing System of Petitions - Official Portal of Vigilance & Anti-Corruption Bureau, Government of Kerala">
    <title>CPSP | Digital Workspace - Vigilance & Anti-Corruption Bureau</title>

    <!-- Google Fonts: Inter and Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">

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

        .metallic-brand-text {
            color: #e2e8f0;
            background: linear-gradient(180deg, #ffffff 0%, #cbd5e1 28%, #f8fafc 48%, #94a3b8 51%, #e2e8f0 76%, #64748b 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 12px rgba(203, 213, 225, 0.12);
        }

        /* Deep navy background with subtle teal and blue light */
        .page-bg {
            background-color: #060d19;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(13, 148, 136, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 45% 65%, rgba(16, 185, 129, 0.10) 0%, transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(14, 165, 233, 0.08) 0%, transparent 50%),
                linear-gradient(180deg, #040812 0%, #060d19 50%, #050b16 100%);
            position: relative;
        }

        .workflow-node-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.625rem;
        }

        .workflow-node-icon {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d1d5db;
        }

        /* ========================================================= */
        /* NEUMORPHIC FLOATING CARD (Requested by user)              */
        /* ========================================================= */
        :root {
            --neu-card-bg: #f5f6f8;
            --neu-shadow-dark: #cbced4;
            --neu-shadow-light: #ffffff;
        }

        .neu-login-card {
            background-color: var(--neu-card-bg);
            border-radius: 1.25rem;
            padding: 2rem 1.75rem;
            box-shadow: 
                0 25px 60px -12px rgba(0, 0, 0, 0.75),
                0 10px 25px -5px rgba(0, 0, 0, 0.4),
                inset 1px 1px 2px rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        /* Inset Neumorphic Input Fields */
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

    </style>
</head>

<body class="h-full page-bg" x-data="{ showPassword: false, loading: false }">
    <div class="min-h-screen flex flex-col relative z-10">
        <main class="w-full flex-1 max-w-[1376px] mx-auto px-6 sm:px-8 py-10 lg:py-12 grid items-center gap-12 lg:grid-cols-[1fr_360px]">
            <section class="w-full max-w-lg">
                <div class="flex items-center gap-4 mb-12">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-md shadow-teal-500/30">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div class="min-w-0 leading-tight">
                        <h2 class="metallic-brand-text font-serif-display text-2xl sm:text-3xl font-bold tracking-wide">CPSP</h2>
                        <p class="metallic-brand-text mt-1 text-base sm:text-lg font-semibold tracking-wide">Vigilance &amp; Anti-Corruption Bureau</p>
                        <div class="mt-1 text-xs sm:text-sm text-sky-400 font-medium">Centralized Processing System of Petitions Cell</div>
                    </div>
                </div>

                <div>
                    <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight leading-[1.12] font-serif-display">
                        Process petitions.<br>Track every case.
                    </h1>
                    <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed max-w-md">
                        Monitor updates across case types and generate reports—all in one secure workspace.
                    </p>
                </div>

                <div class="grid grid-cols-4 gap-3 sm:gap-5 mt-9 max-w-md text-center">
                    <div class="workflow-node-pill">
                        <div class="workflow-node-icon"><i data-lucide="file-plus-2" class="w-5 h-5"></i></div>
                        <span class="text-xs font-semibold text-slate-300">Register</span>
                    </div>
                    <div class="workflow-node-pill">
                        <div class="workflow-node-icon"><i data-lucide="search-check" class="w-5 h-5"></i></div>
                        <span class="text-xs font-semibold text-slate-300">Review</span>
                    </div>
                    <div class="workflow-node-pill">
                        <div class="workflow-node-icon"><i data-lucide="send" class="w-5 h-5"></i></div>
                        <span class="text-xs font-semibold text-slate-300">Forward</span>
                    </div>
                    <div class="workflow-node-pill">
                        <div class="workflow-node-icon"><i data-lucide="check-circle-2" class="w-5 h-5"></i></div>
                        <span class="text-xs font-semibold text-slate-300">Resolve</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 text-xs text-slate-400 font-medium mt-9">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                    <span>Secure</span>
                    <span class="text-slate-600">•</span>
                    <span>Reliable</span>
                    <span class="text-slate-600">•</span>
                    <span>Accountable</span>
                </div>
            </section>

            <!-- RIGHT SECTION: Neumorphic Floating Card (Matching Reference Specs) -->
            <section class="w-full max-w-[360px] mx-auto z-10">
                <div class="neu-login-card">

                    <div class="flex justify-center mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-md shadow-teal-500/30">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <!-- Welcome heading -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl sm:text-[1.75rem] font-bold text-slate-900 tracking-tight font-serif-display leading-tight">
                            Welcome Back
                        </h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
                            Enter your credentials to continue to your workspace.
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
                                PEN Number
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
                                Password
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
                                <span class="text-xs text-slate-600 group-hover:text-slate-900 transition-colors">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" 
                                   class="text-xs font-semibold text-teal-700 hover:text-teal-800 hover:underline transition-colors">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Sign In Button (Vibrant Emerald Gradient Pill) -->
                        <div class="pt-2">
                            <button type="submit" 
                                    :disabled="loading" 
                                    class="btn-emerald-submit">
                                <span x-show="!loading" class="inline-flex items-center gap-2">
                                    <span>Sign In</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </span>
                                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                    <span>Authenticating...</span>
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </section>
        </main>

        <!-- ========================================================= -->
        <!-- BOTTOM FOOTER: Official VACB Government Attribution      -->
        <!-- ========================================================= -->
        <footer class="w-full px-6 py-4 text-center text-xs text-slate-500 z-10">
            <p>Designed by <span class="text-slate-300 font-semibold">Software Development Division</span><br>VACB Directorate, Kerala.</p>
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
