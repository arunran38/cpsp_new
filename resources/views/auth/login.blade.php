@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="w-full max-w-md p-8 bg-white border border-slate-200 rounded-3xl shadow-sm">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">System Login</h2>
            <p class="text-sm text-slate-500 mt-2">Sign in to access your account</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- PEN Number -->
        <div>
            <x-input-label for="pen" :value="__('PEN Number')" />
            <x-text-input id="pen" class="block mt-1 w-full" type="text" name="pen" :value="old('pen')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('pen')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-indigo-600 hover:bg-indigo-700">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
    </div>
</div>
@endsection
