<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Success Message -->
    @if (session('status'))
        <div class="mb-4 p-4 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ __('Email sent successfully!') }}
                    </h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>{{ __('We have sent a password reset link to your email address. Please check your inbox (and spam folder) and click the link to reset your password.') }}</p>
                        <p class="mt-2 font-semibold">{{ __('You can request another reset link in 5 minutes.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Throttle Error -->
    @if ($errors->has('email') && str_contains($errors->first('email'), 'Too Many Attempts'))
        <div class="mb-4 p-4 rounded-md bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('Too many attempts') }}
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>{{ __('You have already requested a password reset link. Please wait 5 minutes before trying again.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="resetForm">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                :disabled="session('status') ? true : false"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (session('status'))
                <button type="button" disabled class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed opacity-50">
                    {{ __('Email Sent - Wait 5 Minutes') }}
                </button>
            @else
                <x-primary-button id="submitBtn">
                    {{ __('Email Password Reset Link') }}
                </x-primary-button>
            @endif
        </div>
    </form>

    @if (session('status'))
        <script>
            // Disable form for 5 minutes
            setTimeout(function() {
                window.location.reload();
            }, 300000); // 5 minutes
        </script>
    @endif
</x-guest-layout>
