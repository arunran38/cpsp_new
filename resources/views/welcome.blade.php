<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CPSP</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('/images/login-bg.jpg') no-repeat center center fixed;
            background-color: #0b0717; /* Fallback dark background */
            background-size: cover;
            color: #ffffff;
        }
        
        .login-card {
            background: rgba(26, 31, 39, 0.85); /* Dark solid-ish glass */
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 360px;
            padding: 2.5rem 2.5rem;
            position: relative;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .avatar-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px auto;
            background: transparent;
            overflow: hidden;
        }

        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .form-label {
            font-size: 10px;
            color: #ffffff;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            height: 40px;
        }

        .input-group:focus-within {
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .form-control {
            background: #eef1f5; /* Light bluish-white input area */
            border: none;
            color: #000000;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: none !important;
            flex: 1;
        }

        .form-control::placeholder {
            color: #888888;
        }

        .input-group-text {
            background: transparent;
            border: none;
            border-left: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-group-text i {
            font-size: 14px;
        }

        .mb-custom {
            margin-bottom: 20px;
        }

        .form-check {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0;
        }

        .form-check-input {
            background-color: transparent;
            border-color: rgba(255, 255, 255, 0.4);
            border-radius: 3px;
            margin-top: 2px;
            margin-right: 8px;
            width: 14px;
            height: 14px;
        }

        .form-check-input:checked {
            background-color: #9061f9;
            border-color: #9061f9;
        }

        .form-check-label {
            font-size: 11px;
            color: #ffffff;
            cursor: pointer;
            font-weight: 500;
        }

        .mb-4-spacer {
            margin-bottom: 25px;
            margin-top: 15px;
        }

        .btn-login {
            background: #9b72e6; /* Solid bright purple matching the image */
            border: none;
            border-radius: 20px; /* Pill layout */
            color: #ffffff;
            padding: 10px;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(155, 114, 230, 0.3);
        }

        .btn-login:hover {
            background: #8757db;
            color: #ffffff;
        }

        .invalid-feedback {
            font-size: 11px;
            margin-top: 6px;
            padding-left: 4px;
            display: block;
            color: #ff6b6b !important;
        }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-card">
        <div class="logo-text">CPSP</div>
        
        <div class="avatar-container">
            <img src="{{ asset('images/logo.png') }}" alt="Kerala VACB Logo">
        </div>

        <form method="POST" action="">
            @csrf

            <div class="mb-custom">
                <label class="form-label">PEN</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="pen" placeholder="963950" value="{{ old('pen') }}" autofocus>
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                </div>
                @if($errors->has('pen'))
                    <div class="invalid-feedback">{{ $errors->first('pen') }}</div>
                @endif
            </div>

            <div class="mb-custom">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="password" placeholder="••••••">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                </div>
                @if($errors->has('password'))
                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                @endif
            </div>

            <div class="form-check mb-4-spacer">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <div>
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                Login
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-login mt-2">
            Admin Dashboard
            </a>
            <a href="{{ route('user.dashboard') }}" class="btn btn-login mt-2">
            User Dashboard
            </a>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
