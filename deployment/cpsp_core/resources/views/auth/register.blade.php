<x-guest-layout>
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                {{ __('Create Account') }}
            </h1>
            <p class="text-gray-600 text-sm">{{ __('Join us and get started today') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div class="relative">
                <x-input-label for="name" :value="__('Full Name')" class="text-gray-700 font-medium text-sm mb-2 block" />
                <div class="relative">
                    <span class="absolute left-3 top-3 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                    <x-text-input id="name" class="block w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="{{ __('Enter your full name') }}" />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-500 text-sm" />
            </div>

            <!-- Email Address -->
            <div class="relative">
                <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-medium text-sm mb-2 block" />
                <div class="relative">
                    <span class="absolute left-3 top-3 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <x-text-input id="email" class="block w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="{{ __('Enter your email') }}" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" />
            </div>

            <!-- Password -->
            <div class="relative">
                <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium text-sm mb-2 block" />
                <div class="relative">
                    <span class="absolute left-3 top-3 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <x-text-input id="password" class="block w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" placeholder="{{ __('Create a strong password') }}" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm" />
            </div>

            <!-- Confirm Password -->
            <div class="relative">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-medium text-sm mb-2 block" />
                <div class="relative">
                    <span class="absolute left-3 top-3 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    <x-text-input id="password_confirmation" class="block w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm your password') }}" />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500 text-sm" />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg hover:shadow-xl">
                {{ __('Create Account') }}
            </button>

            <!-- Login Link -->
            <div class="text-center pt-2">
                <p class="text-gray-600 text-sm">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold hover:underline transition duration-200">
                        {{ __('Sign In') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
