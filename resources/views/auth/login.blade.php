<style>
    body, html {
        font-family: 'Cambria', serif !important;
    }

    .font-size {
        font-family: 'Cambria', serif !important;
    }

    .login-container {
        margin: 0 auto;
    }

    @media (min-width: 1024px) {
        .login-container {
            margin-left: 0;
            margin-right: 33%;
        }
    }
</style>

<x-guest-layout>
    <div class="flex flex-col min-h-screen">
        <div class="relative flex-1 flex items-center justify-center bg-cover bg-center" style="background-image: url('{{ asset('assets/image.png') }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, 0.5);"></div>
            <div class="login-container relative z-10 max-w-md backdrop-blur-md shadow-2xl rounded-2xl p-8 sm:p-10 w-full space-y-6" style="background-color: white;">                
                <div class="flex justify-center" style="flex-direction: column; align-items: center; text-align: center; gap: 4px; margin-bottom: 20px;">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-20 w-auto bg-white" />
                    <h2 style="font-size: 20px; font-weight: 700;">Fleet Management System</h2>
                </div>
                <x-auth-session-status class="mb-6 text-center" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="font-medium text-white-500" style="color: rgb(0, 0, 0);" />
                        <x-text-input id="email" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" class="font-medium text-gray-700"  style="color: rgb(0, 0, 0);" />
                        <x-text-input id="password" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                        <label for="remember_me" class="text-sm text-gray-600" style="color: rgb(0, 0, 0);">{{ __('Remember me') }}</label>
                    </div>

                    <div class="flex flex-col items-center gap-4 mt-4">
                        @if (Route::has('password.request'))
                            <a class="text-sm text-indigo-600 hover:text-indigo-800 transition" href="{{ route('password.request') }}" style="color: rgb(61, 24, 192);">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif

                        <x-primary-button
                            class="w-full sm:w-auto px-6 py-3 justify-center bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 rounded-lg shadow-md text-white font-semibold"
                            style="background-color: #1184e2f9;">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
        <footer class="absolute z-20 w-full py-4 text-center text-white text-sm" style="background-color: rgba(0, 0, 0, 0.2); bottom: 0;">
            <p class="mb-1">
                <b>Fleet Management System</b> © <span id="currentYear"></span> All Rights Reserved
            </p>
            <p>
                Developed & Maintained by <b>IT Department of Explore Holdings (Pvt) Ltd</b>
            </p>
        </footer>
    </div>
</x-guest-layout>

<script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>