<nav x-data="{ open: false }" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('dashboard.dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('dashboard.stats')" :active="request()->routeIs('dashboard.stats')">
                        {{ __('dashboard.stats') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reminders.index')" :active="request()->routeIs('reminders.index')">
                        {{ __('dashboard.reminders') }}
                    </x-nav-link>
                    <x-nav-link :href="route('comments.page')" :active="request()->routeIs('comments.page')">
                        {{ __('dashboard.comments') }}
                    </x-nav-link>
                    @if(Auth::user()->is_admin)
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ __('dashboard.users') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Theme Toggle -->
                <button
                    id="theme-toggle"
                    type="button"
                    class="inline-flex items-center justify-center p-2 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 hover:shadow-md focus:outline-none transition-all duration-200"
                    aria-label="Toggle dark mode"
                >
                    <!-- Sun Icon (Light Mode) -->
                    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                    <!-- Moon Icon (Dark Mode) -->
                    <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>

                <!-- Language Switcher -->
                <x-dropdown align="right" width="32">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 hover:shadow-md focus:outline-none transition-all duration-200">
                            <span class="text-base mr-1">🌐</span>
                            <div>{{ strtoupper(app()->getLocale()) }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('language.switch', 'en')">
                            <span class="inline-flex items-center">
                                <span class="mr-2">🇬🇧</span> English
                            </span>
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('language.switch', 'da')">
                            <span class="inline-flex items-center">
                                <span class="mr-2">🇩🇰</span> Dansk
                            </span>
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 hover:shadow-md focus:outline-none transition-all duration-200">
                            <div>{{ Auth::user()->name }}</div>
                            @if(!Auth::user()->canViewAllInvoices())
                                <span class="ml-2 text-xs px-2 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300">
                                    {{ ucfirst(Auth::user()->role) }}
                                </span>
                            @endif

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('dashboard.profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('dashboard.log_out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition-all duration-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('dashboard.dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard.stats')" :active="request()->routeIs('dashboard.stats')">
                {{ __('dashboard.stats') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reminders.index')" :active="request()->routeIs('reminders.index')">
                {{ __('dashboard.reminders') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('comments.page')" :active="request()->routeIs('comments.page')">
                {{ __('dashboard.comments') }}
            </x-responsive-nav-link>
            @if(Auth::user()->is_admin)
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('dashboard.users') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Theme Toggle (Mobile) -->
                <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 uppercase">Theme</div>
                <button
                    id="theme-toggle-mobile"
                    type="button"
                    class="w-full text-start flex items-center px-4 py-2 text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out"
                >
                    <!-- Sun Icon (Light Mode) -->
                    <svg id="theme-toggle-light-icon-mobile" class="w-5 h-5 mr-3 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                    <!-- Moon Icon (Dark Mode) -->
                    <svg id="theme-toggle-dark-icon-mobile" class="w-5 h-5 mr-3 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <span id="theme-toggle-text-mobile">Dark Mode</span>
                </button>

                <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>

                <!-- Language Links -->
                <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 uppercase">Language</div>
                <x-responsive-nav-link :href="route('language.switch', 'en')" :active="app()->getLocale() === 'en'">
                    🇬🇧 English
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('language.switch', 'da')" :active="app()->getLocale() === 'da'">
                    🇩🇰 Dansk
                </x-responsive-nav-link>

                <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('dashboard.profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('dashboard.log_out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Theme Toggle Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Desktop toggle elements
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');

        // Mobile toggle elements
        const themeToggleMobileBtn = document.getElementById('theme-toggle-mobile');
        const themeToggleLightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');
        const themeToggleDarkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
        const themeToggleTextMobile = document.getElementById('theme-toggle-text-mobile');

        // Function to update icons based on current theme
        function updateIcons() {
            const isDark = document.documentElement.classList.contains('dark');

            // Update desktop icons
            if (isDark) {
                themeToggleLightIcon.classList.remove('hidden');
                themeToggleDarkIcon.classList.add('hidden');
            } else {
                themeToggleDarkIcon.classList.remove('hidden');
                themeToggleLightIcon.classList.add('hidden');
            }

            // Update mobile icons and text
            if (isDark) {
                themeToggleLightIconMobile.classList.remove('hidden');
                themeToggleDarkIconMobile.classList.add('hidden');
                themeToggleTextMobile.textContent = 'Light Mode';
            } else {
                themeToggleDarkIconMobile.classList.remove('hidden');
                themeToggleLightIconMobile.classList.add('hidden');
                themeToggleTextMobile.textContent = 'Dark Mode';
            }
        }

        // Function to toggle theme
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');

            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }

            // Update icons with smooth transition
            updateIcons();
        }

        // Initialize icons on page load
        updateIcons();

        // Desktop toggle
        themeToggleBtn.addEventListener('click', toggleTheme);

        // Mobile toggle
        themeToggleMobileBtn.addEventListener('click', toggleTheme);
    });
</script>
