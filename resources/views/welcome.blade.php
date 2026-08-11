<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome CPSP</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Vite (Laravel) -->
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
            font-family: 'Inter', sans-serif;
            background-color: #020617;
            /* Slate 950 */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            padding: 2rem 1rem;
        }

        /* Dashbaord-consistent Ambient background */
        .ambient-bg {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            animation: pulse 10s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes pulse {
            0% {
                transform: scale(1) translate(0, 0);
                opacity: 0.1;
            }

            100% {
                transform: scale(1.1) translate(20px, 20px);
                opacity: 0.2;
            }
        }

        .orb-1 {
            width: 40rem;
            height: 40rem;
            background: #4f46e5;
            top: -10rem;
            left: -10rem;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 30rem;
            height: 30rem;
            background: #9333ea;
            top: 50%;
            right: -5rem;
            animation-delay: -2s;
        }

        .orb-3 {
            width: 35rem;
            height: 35rem;
            background: #1e293b;
            bottom: -5rem;
            left: 20%;
            animation-delay: -5s;
        }

        .welcome-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Dashboard-style Branding */
        .brand-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2.5rem;
            animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
        }

        .logo-text {
            text-align: center;
        }

        .logo-text h1 {
            font-size: 1.875rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1;
        }

        .logo-text h1 span {
            color: #818cf8;
        }

        .logo-text p {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .login-card {
            background: rgba(15, 23, 42, 0.7);
            /* slate-900/70 */
            backdrop-filter: blur(24px);
            border: 1px solid rgba(51, 65, 85, 0.5);
            /* slate-800/50 */
            border-radius: 2rem;
            padding: 3rem;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 0.5rem;
            margin-left: 0.25rem;
        }

        .input-field {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(2, 6, 23, 0.5);
            /* slate-950/50 */
            border: 1px solid rgba(51, 65, 85, 0.8);
            border-radius: 1rem;
            color: #f1f5f9;
            font-size: 0.9375rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-field:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(2, 6, 23, 0.8);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            width: 1.125rem;
            height: 1.125rem;
            transition: color 0.3s ease;
        }

        .input-field:focus+.input-icon {
            color: #818cf8;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #818cf8;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            margin-bottom: 2.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.875rem;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
        }

        .remember-me:hover {
            color: #f1f5f9;
        }

        .checkbox {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: #6366f1;
            border-radius: 4px;
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to br, #6366f1, #4f46e5);
            border: none;
            border-radius: 1rem;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px -10px rgba(79, 70, 229, 0.5);
            filter: brightness(1.1);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .footer {
            text-align: center;
            margin-top: auto;
            padding: 3rem 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
            z-index: 10;
            letter-spacing: 0.025em;
            width: 100%;
            animation: fadeIn 1s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .footer span {
            color: #818cf8;
            font-weight: 700;
        }

        .footer-sep {
            height: 1px;
            width: 2rem;
            background: rgba(51, 65, 85, 0.5);
            margin: 0.5rem auto 1rem;
        }
    </style>
</head>

<body x-data="{ showPassword: false, loading: false }">

    <!-- Ambient Background Effects -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="welcome-container">
        <!-- Dashboard-style Branding -->
        <div class="brand-logo">
            <div class="logo-icon">
                <i data-lucide="scroll-text" class="w-8 h-8 text-white"></i>
            </div>
            <div class="logo-text">
                <h1>CPSP</h1>
                <p>Centralized Processing System of Petitions Cell</p>
            </div>
        </div>

        <div class="login-card">
            <form method="POST" action="{{ route('login') }}" @submit="loading = true">
                @csrf

                <div class="input-group">
                    <label for="pen" class="input-label">PEN Number</label>
                    <div class="input-wrapper">
                        <input type="text" name="pen" id="pen" value="{{ old('pen') }}" class="input-field"
                            placeholder="Permanent Employee Number" required autofocus>
                        <i data-lucide="user" class="input-icon"></i>
                    </div>
                    @error('pen')
                        <span
                            class="text-rose-400 text-[11px] font-semibold mt-1.5 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">Password</label>
                    <div class="input-wrapper">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password" class="input-field"
                            placeholder="••••••••" required>
                        <i data-lucide="lock" class="input-icon"></i>
                        <button type="button" @click="showPassword = !showPassword" class="password-toggle">
                            <i data-lucide="eye" x-show="!showPassword" class="w-4 h-4"></i>
                            <i data-lucide="eye-off" x-show="showPassword" x-cloak class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password')
                        <span
                            class="text-rose-400 text-[11px] font-semibold mt-1.5 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                    @enderror
                </div>

                <div class="options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" class="checkbox" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                </div>

                <button type="submit" :disabled="loading" class="login-btn">
                    <span x-show="!loading">Sign In</span>
                    <i x-show="!loading" data-lucide="arrow-right" class="w-4 h-4"></i>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        Authenticating...
                    </span>
                </button>
            </form>
        </div>
    </div>

    <div class="footer">
        <div class="footer-sep"></div>
        Designed by <span>Software Development Division</span><br>
        <span>VACB Directorate</span>, Kerala
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.sweetalert-session')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>