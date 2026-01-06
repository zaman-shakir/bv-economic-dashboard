<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Dark Mode Script (Must run before page renders to prevent flash) -->
        <script>
            // Check for saved theme preference or default to 'light'
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 animated-gradient dark:bg-gray-900 relative overflow-hidden">
            <!-- Theme Toggle Button (Top Right) -->
            <div class="absolute top-4 right-4 z-50">
                <button
                    id="theme-toggle-guest"
                    type="button"
                    class="inline-flex items-center justify-center p-3 rounded-lg text-white/80 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-lg hover:shadow-lg focus:outline-none transition-all duration-200 border border-white/20"
                    aria-label="Toggle dark mode"
                >
                    <!-- Sun Icon (Light Mode) -->
                    <svg id="theme-toggle-light-icon-guest" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                    <!-- Moon Icon (Dark Mode) -->
                    <svg id="theme-toggle-dark-icon-guest" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 4s;"></div>
            </div>

            <!-- Logo -->
            <div class="relative z-10 mb-6 animate-slide-down">
                <a href="/" class="block">
                    <x-application-logo class="w-24 h-24 fill-current text-white drop-shadow-2xl float" />
                </a>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md relative z-10 animate-slide-up">
                <div class="glass-strong px-8 py-10 shadow-elevation-4 rounded-2xl backdrop-blur-2xl border border-white/30">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer Text -->
            <div class="mt-6 text-center text-white/80 dark:text-gray-400 text-sm relative z-10">
                <p class="drop-shadow-lg">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>

        <!-- Theme Toggle Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const themeToggleBtn = document.getElementById('theme-toggle-guest');
                const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon-guest');
                const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon-guest');

                // Function to update icons based on current theme
                function updateIcons() {
                    const isDark = document.documentElement.classList.contains('dark');
                    if (isDark) {
                        themeToggleLightIcon.classList.remove('hidden');
                        themeToggleDarkIcon.classList.add('hidden');
                    } else {
                        themeToggleDarkIcon.classList.remove('hidden');
                        themeToggleLightIcon.classList.add('hidden');
                    }
                }

                // Initialize icons on page load
                updateIcons();

                // Toggle theme when button is clicked
                themeToggleBtn.addEventListener('click', function() {
                    const isDark = document.documentElement.classList.contains('dark');

                    if (isDark) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }

                    // Update icons
                    updateIcons();
                });
            });
        </script>
    </body>
</html>
