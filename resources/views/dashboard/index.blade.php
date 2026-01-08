<x-app-layout>

    <!-- HTMX Script -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <!-- Flatpickr CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>

    <div class="pt-6 pb-12">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            <!-- Top Toolbar: All controls in one row -->
            <div class="mb-6 flex items-center gap-3 w-full">
                <!-- Group 1: Filter Buttons -->
                <div class="flex gap-1.5 flex-1">
                    <a href="{{ route('dashboard', ['filter' => 'all']) }}"
                       class="flex-1 px-1.5 xl:px-2.5 py-1.5 rounded-md text-sm font-medium transition text-center whitespace-nowrap flex items-center justify-center gap-1.5 {{ $currentFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('dashboard.filter_all') }}
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'overdue']) }}"
                       class="flex-1 px-1.5 xl:px-2.5 py-1.5 rounded-md text-sm font-medium transition text-center whitespace-nowrap flex items-center justify-center gap-1.5 {{ $currentFilter === 'overdue' ? 'bg-red-600 text-white' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('dashboard.filter_overdue') }}
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'unpaid']) }}"
                       class="flex-1 px-1.5 xl:px-2.5 py-1.5 rounded-md text-sm font-medium transition text-center whitespace-nowrap flex items-center justify-center gap-1.5 {{ $currentFilter === 'unpaid' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('dashboard.filter_unpaid') }}
                    </a>
                </div>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>

                <!-- Group 2: Grouping Buttons -->
                <div class="flex gap-1.5 flex-1">
                    <a href="{{ route('dashboard', ['filter' => $currentFilter, 'grouping' => 'employee']) }}"
                       class="flex-1 px-1.5 xl:px-2.5 py-1.5 rounded-md text-sm font-medium transition text-center whitespace-nowrap flex items-center justify-center gap-1.5 {{ ($currentGrouping ?? 'employee') === 'employee' ? 'bg-indigo-600 text-white' : 'bg-indigo-200 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-300 dark:hover:bg-indigo-900/70' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        By Employee
                    </a>
                    <a href="{{ route('dashboard', ['filter' => $currentFilter, 'grouping' => 'other_ref']) }}"
                       class="flex-1 px-1.5 xl:px-2.5 py-1.5 rounded-md text-sm font-medium transition text-center whitespace-nowrap flex items-center justify-center gap-1.5 {{ ($currentGrouping ?? 'employee') === 'other_ref' ? 'bg-indigo-600 text-white' : 'bg-indigo-200 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-300 dark:hover:bg-indigo-900/70' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        By Other Ref
                    </a>
                </div>

                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>

                <!-- Group 3: Actions -->
                <div class="flex gap-3 xl:gap-1.5">
                    <select id="employeeFilter" onchange="filterByEmployee(this.value)"
                            class="min-w-[180px] max-w-[320px] px-2.5 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium transition hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                        <option value="">👥 {{ __('dashboard.all_employees') }}</option>
                        @foreach($invoicesByEmployee as $emp)
                            <option value="{{ $emp['employeeNumber'] }}">
                                {{ $emp['employeeName'] }} ({{ $emp['invoiceCount'] }})
                            </option>
                        @endforeach
                    </select>

                    <a href="{{ route('dashboard', request()->all()) }}"
                       class="px-2.5 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-sm font-medium transition hover:bg-gray-300 dark:hover:bg-gray-600 whitespace-nowrap flex items-center gap-1.5"
                       title="{{ __('dashboard.refresh_data') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('dashboard.refresh_data') }}
                    </a>

                    @if($usingDatabase ?? false)
                    <button
                        id="syncButton"
                        onclick="syncNow()"
                        class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap flex items-center gap-1.5"
                    >
                        <svg id="syncIcon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="syncButtonText">Sync now</span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Loading Indicator & Sync Progress -->
            <div class="flex items-center justify-center gap-4 mb-4">
                <div id="loading" class="htmx-indicator text-sm text-blue-600 dark:text-blue-400 font-medium">
                    {{ __('dashboard.loading_data') }}
                </div>

                <!-- Progress Bar (hidden by default) -->
                @if($usingDatabase ?? false)
                <div id="syncProgress" class="hidden w-full max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-lg border border-red-200 dark:border-red-800">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden mb-2">
                            <div id="progressBar" class="bg-red-500 h-4 transition-all duration-300 rounded-full" style="width: 0%"></div>
                        </div>
                        <div id="progressText" class="text-sm text-gray-700 dark:text-gray-300 font-medium text-center"></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Combined Info Banners (Side by Side) -->
            @if($usingDatabase ?? false)
            <div class="mb-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
                <!-- Sync Status (Left) -->
                <div class="bg-gradient-to-r from-red-50 to-rose-50 dark:from-gray-800 dark:to-gray-700 border border-red-200 dark:border-red-800 rounded-lg p-3 shadow-sm">
                    <div class="flex items-center gap-2 flex-wrap text-sm text-gray-600 dark:text-gray-400">
                        @if($lastSyncedAt && $lastSyncedAt->diffInMinutes(now()) < 30)
                            <div class="flex items-center gap-2">
                                <span class="flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                                <span class="font-medium text-red-700 dark:text-red-400">{{ __('dashboard.data_up_to_date') }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="flex h-3 w-3 rounded-full bg-yellow-500"></span>
                                <span class="font-medium text-yellow-700 dark:text-yellow-400">Sync recommended</span>
                            </div>
                        @endif
                        @if($lastSyncedAt)
                            <span>• Last: <strong>{{ $lastSyncedAt->diffForHumans() }}</strong> <span class="text-xs">({{ $lastSyncedAt->format('d M H:i') }})</span></span>
                        @else
                            <span>• <strong>Never synced</strong></span>
                        @endif
                        <span>• Total synced: <strong>{{ number_format($syncStats['total_invoices'] ?? 0) }}</strong></span>
                        @if($nextSyncAt)
                        @if($nextSyncAt->isPast())
                            <span>• Next: <strong class="text-yellow-600 dark:text-yellow-400">Overdue</strong></span>
                        @else
                            <span>• Next: <strong>{{ $nextSyncAt->diffForHumans() }}</strong> <span class="text-xs">({{ $nextSyncAt->format('H:i') }})</span></span>
                        @endif
                        @endif
                    </div>
                </div>

                <!-- Data View Info (Right) -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg p-3 shadow-sm">
                    <div class="flex flex-col gap-1 text-sm text-gray-700 dark:text-gray-300">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">📊</span>
                                <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded text-xs font-medium">Database</span>
                            </div>
                            <span>• All <strong class="text-blue-700 dark:text-blue-400">{{ number_format($syncStats['total_invoices'] ?? 0) }}</strong></span>
                            <span>• Filter: <strong class="text-blue-700 dark:text-blue-400">
                                @if($currentFilter === 'all')All
                                @elseif($currentFilter === 'overdue')Overdue
                                @elseif($currentFilter === 'unpaid')Unpaid
                                @endif
                            </strong></span>
                            <span>• Showing: <strong class="text-blue-700 dark:text-blue-400">{{ $invoicesByEmployee->sum('invoiceCount') }}</strong></span>
                        </div>

                        @if(isset($dataQuality) && $dataQuality['has_unassigned'])
                        <div class="text-yellow-700 dark:text-yellow-400 text-xs">
                            ⚠️ {{ $dataQuality['message'] }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Second Toolbar: Search, Sort, Export, Bulk Actions -->
            <div class="mb-4 card-glass p-4">
                <!-- Top Row: Search, Date Inputs, Sort, Bulk -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search -->
                    <div class="flex-1 min-w-[250px]">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchInput" value="{{ $search ?? '' }}"
                                   placeholder="{{ __('dashboard.search_invoices') }} (Press Enter to search)"
                                   onkeypress="if(event.key === 'Enter') applySearch()"
                                   class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Date Inputs -->
                    <div class="flex items-center gap-2">
                        <input type="date" id="dateFrom" value="{{ $dateFrom ?? '' }}"
                               placeholder="dd-mm-yyyy"
                               lang="en-GB"
                               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">to</span>
                        <input type="date" id="dateTo" value="{{ $dateTo ?? '' }}"
                               placeholder="dd-mm-yyyy"
                               lang="en-GB"
                               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-500 text-sm">
                        <button onclick="filterByDateRange()"
                                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-sm font-medium">
                            📅 Filter
                        </button>
                        <button onclick="clearDateFilter()"
                                class="px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition text-sm font-medium">
                            Clear
                        </button>
                    </div>

                    <!-- Sort -->
                    <select id="sortBy" onchange="sortInvoices(this.value)"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('dashboard.sort_by') }}</option>
                        <option value="days_desc">{{ __('dashboard.sort_days_desc') }}</option>
                        <option value="days_asc">{{ __('dashboard.sort_days_asc') }}</option>
                        <option value="amount_desc">{{ __('dashboard.sort_amount_desc') }}</option>
                        <option value="amount_asc">{{ __('dashboard.sort_amount_asc') }}</option>
                        <option value="customer">{{ __('dashboard.sort_customer') }}</option>
                        <option value="recent_comments">{{ __('dashboard.sort_recent_comments') }}</option>
                    </select>

                    <!-- Bulk Actions -->
                    <button onclick="toggleBulkMode()" id="bulkModeBtn"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ __('dashboard.bulk_actions') }}
                    </button>
                </div>

                <!-- Bottom Row: Quick Date Presets -->
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Quick:</span>
                    @php
                        $dateFrom = request('date_from');
                        $dateTo = request('date_to');
                        $today = now()->format('Y-m-d');
                        $last3Days = now()->subDays(3)->format('Y-m-d');
                        $last7Days = now()->subDays(7)->format('Y-m-d');
                        $thisMonthStart = now()->startOfMonth()->format('Y-m-d');
                        $lastMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
                        $lastMonthEnd = now()->subMonth()->endOfMonth()->format('Y-m-d');
                        $quarterStart = now()->firstOfQuarter()->format('Y-m-d');

                        $isToday = $dateFrom === $today && $dateTo === $today;
                        $isLast3Days = $dateFrom === $last3Days && $dateTo === $today;
                        $isLastWeek = $dateFrom === $last7Days && $dateTo === $today;
                        $isThisMonth = $dateFrom === $thisMonthStart && $dateTo === $today;
                        $isLastMonth = $dateFrom === $lastMonthStart && $dateTo === $lastMonthEnd;
                        $isThisQuarter = $dateFrom === $quarterStart && $dateTo === $today;
                    @endphp
                    <button onclick="setDatePreset('today')" class="px-2 py-1 text-xs {{ $isToday ? 'bg-blue-600 text-white' : 'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300' }} rounded transition">Today</button>
                    <button onclick="setDatePreset('last_3_days')" class="px-2 py-1 text-xs {{ $isLast3Days ? 'bg-blue-600 text-white' : 'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300' }} rounded transition">Last 3 Days</button>
                    <button onclick="setDatePreset('last_week')" class="px-2 py-1 text-xs {{ $isLastWeek ? 'bg-blue-600 text-white' : 'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300' }} rounded transition">Last Week</button>
                    <button onclick="setDatePreset('this_month')" class="px-2 py-1 text-xs {{ $isThisMonth ? 'bg-blue-600 text-white' : 'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300' }} rounded transition">This Month</button>
                </div>

                <!-- Bulk Actions Panel (Hidden by default) -->
                <div id="bulkActionsPanel" class="hidden w-full mt-3 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-purple-900 dark:text-purple-300">
                            <span id="selectedCount">0</span> {{ __('dashboard.selected') }}
                        </span>
                        <button onclick="sendBulkReminders()" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded transition">
                            {{ __('dashboard.send_bulk_reminders') }}
                        </button>
                        <button onclick="selectAll()" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded transition">
                            {{ __('dashboard.select_all') }}
                        </button>
                        <button onclick="deselectAll()" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded transition">
                            {{ __('dashboard.deselect_all') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content: Full Width Invoice List -->
            <div>
                <div id="invoice-list">
                    @include('dashboard.partials.invoice-list', ['invoicesByEmployee' => $invoicesByEmployee, 'currentFilter' => $currentFilter])
                </div>
            </div>
        </div>
    </div>

    <style>
        .htmx-indicator {
            display: none;
        }
        .htmx-request .htmx-indicator {
            display: block;
        }

        /* Pulse Animation for Comment Badge */
        @keyframes pulse-badge {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .pulse-badge {
            animation: pulse-badge 0.6s ease-in-out 3;
        }
    </style>

    <script>
        // Employee Filter Function
        function filterByEmployee(employeeNumber) {
            const sections = document.querySelectorAll('[data-employee-section]');
            sections.forEach(section => {
                if (employeeNumber === '' || section.dataset.employeeSection === employeeNumber) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        }

        // Toggle Employee Section
        function toggleSection(employeeNumber) {
            const content = document.getElementById(`employee-${employeeNumber}`);
            const icon = document.getElementById(`icon-${employeeNumber}`);
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

        async function sendReminder(invoiceNumber, customerNumber, button) {
            // Confirm action
            if (!confirm('{{ __("dashboard.confirm_send_reminder") }}')) {
                return;
            }

            // Disable button and show loading state
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '⏳ {{ __("dashboard.sending_reminder") }}';

            try {
                const response = await fetch('{{ route("reminders.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        invoice_number: invoiceNumber,
                        customer_number: customerNumber,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    button.innerHTML = '✅ {{ __("dashboard.reminder_sent_successfully") }}';
                    button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    button.classList.add('bg-green-600');

                    // Reset button after 3 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-600');
                        button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        button.disabled = false;
                    }, 3000);
                } else {
                    // Show error message
                    alert(data.message);
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            } catch (error) {
                // Show error
                alert('{{ __("dashboard.reminder_send_failed") }}: ' + error.message);
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        /**
         * Open e-conomic invoice in a popup window
         */
        function openInvoicePopup(invoiceNumber) {
            const url = `https://secure.e-conomic.com/secure/include/visfaktura.asp?ops=29217799&bogf=1&faknr=${invoiceNumber}`;

            // Calculate popup dimensions (80% of screen)
            const width = Math.floor(window.screen.width * 0.8);
            const height = Math.floor(window.screen.height * 0.8);

            // Center the popup
            const left = Math.floor((window.screen.width - width) / 2);
            const top = Math.floor((window.screen.height - height) / 2);

            // Open popup with specific features
            const popup = window.open(
                url,
                `invoice_${invoiceNumber}`,
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes,toolbar=no,menubar=no,location=no`
            );

            // Focus the popup if it was successfully opened
            if (popup) {
                popup.focus();
            }
        }

        async function sendEmployeeReminder(employeeNumber, button) {
            // Confirm action
            if (!confirm('{{ __("dashboard.confirm_send_employee_reminder") }}')) {
                return;
            }

            // Disable button and show loading state
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '⏳ {{ __("dashboard.sending_reminder") }}';

            try {
                const response = await fetch('{{ route("reminders.send-employee") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        employee_number: employeeNumber,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    button.innerHTML = '✅ {{ __("dashboard.employee_reminder_sent") }}';
                    button.classList.remove('bg-orange-600', 'hover:bg-orange-700');
                    button.classList.add('bg-green-600');

                    // Reset button after 3 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-600');
                        button.classList.add('bg-orange-600', 'hover:bg-orange-700');
                        button.disabled = false;
                    }, 3000);
                } else {
                    // Show error message
                    alert(data.message);
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            } catch (error) {
                // Show error
                alert('{{ __("dashboard.reminder_send_failed") }}: ' + error.message);
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        // Apply Search (SERVER-SIDE)
        function applySearch() {
            const searchTerm = document.getElementById('searchInput').value;
            const currentFilter = '{{ $currentFilter }}';
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;

            let url = new URL(window.location.href);
            url.searchParams.set('filter', currentFilter);

            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }

            if (dateFrom) url.searchParams.set('date_from', dateFrom);
            if (dateTo) url.searchParams.set('date_to', dateTo);

            // Reload page with new parameters
            window.location.href = url.toString();
        }

        // Legacy function for compatibility (client-side filtering kept as fallback)
        function searchInvoices() {
            // This is kept for backward compatibility
            // But users should press Enter to trigger server-side search
        }

        // Date Range Filter (SERVER-SIDE)
        function filterByDateRange() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;

            if (!dateFrom || !dateTo) {
                alert('Please select both start and end dates');
                return;
            }

            const fromDate = new Date(dateFrom);
            const toDate = new Date(dateTo);

            if (fromDate > toDate) {
                alert('Start date must be before end date');
                return;
            }

            // Build URL with current filter and date range
            const currentFilter = '{{ $currentFilter }}';
            const searchTerm = document.getElementById('searchInput').value;

            let url = new URL(window.location.href);
            url.searchParams.set('filter', currentFilter);
            url.searchParams.set('date_from', dateFrom);
            url.searchParams.set('date_to', dateTo);

            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            }

            // Reload page with new parameters
            window.location.href = url.toString();
        }

        // Clear Date Filter (SERVER-SIDE)
        function clearDateFilter() {
            // Build URL without date parameters
            const currentFilter = '{{ $currentFilter }}';
            const searchTerm = document.getElementById('searchInput').value;

            let url = new URL(window.location.href);
            url.searchParams.set('filter', currentFilter);
            url.searchParams.delete('date_from');
            url.searchParams.delete('date_to');

            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }

            // Reload page
            window.location.href = url.toString();
        }

        function setDatePreset(preset) {
            const today = new Date();
            let dateFrom, dateTo;

            switch(preset) {
                case 'today':
                    dateFrom = today;
                    dateTo = today;
                    break;
                case 'last_3_days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 3);
                    dateTo = today;
                    break;
                case 'last_week':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 7);
                    dateTo = today;
                    break;
                case 'this_month':
                    dateFrom = new Date(today.getFullYear(), today.getMonth(), 1);
                    dateTo = today;
                    break;
                case 'last_month':
                    dateFrom = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    dateTo = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'this_quarter':
                    const currentQuarter = Math.floor(today.getMonth() / 3);
                    dateFrom = new Date(today.getFullYear(), currentQuarter * 3, 1);
                    dateTo = today;
                    break;
            }

            // Format dates as YYYY-MM-DD
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            // Update the input fields
            document.getElementById('dateFrom').value = formatDate(dateFrom);
            document.getElementById('dateTo').value = formatDate(dateTo);

            // Update flatpickr instances if they exist
            const dateFromPicker = document.querySelector("#dateFrom")._flatpickr;
            const dateToPicker = document.querySelector("#dateTo")._flatpickr;
            if (dateFromPicker) dateFromPicker.setDate(formatDate(dateFrom));
            if (dateToPicker) dateToPicker.setDate(formatDate(dateTo));

            // Automatically apply the filter
            filterByDateRange();
        }

        // Sort Invoices
        function sortInvoices(sortBy) {
            const employeeSections = document.querySelectorAll('[data-employee-section]');

            employeeSections.forEach(section => {
                const tbody = section.querySelector('tbody');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    let aVal, bVal;

                    switch(sortBy) {
                        case 'days_desc':
                            // Most overdue first
                            aVal = parseInt(a.querySelector('.inline-flex')?.textContent.trim()) || 0;
                            bVal = parseInt(b.querySelector('.inline-flex')?.textContent.trim()) || 0;
                            return bVal - aVal;

                        case 'days_asc':
                            // Least overdue first
                            aVal = parseInt(a.querySelector('.inline-flex')?.textContent.trim()) || 0;
                            bVal = parseInt(b.querySelector('.inline-flex')?.textContent.trim()) || 0;
                            return aVal - bVal;

                        case 'amount_desc':
                            // Highest amount first (cell 6 after adding new columns)
                            aVal = parseFloat(a.cells[6]?.textContent.replace(/[.,]/g, '')) || 0;
                            bVal = parseFloat(b.cells[6]?.textContent.replace(/[.,]/g, '')) || 0;
                            return bVal - aVal;

                        case 'amount_asc':
                            // Lowest amount first (cell 6 after adding new columns)
                            aVal = parseFloat(a.cells[6]?.textContent.replace(/[.,]/g, '')) || 0;
                            bVal = parseFloat(b.cells[6]?.textContent.replace(/[.,]/g, '')) || 0;
                            return aVal - bVal;

                        case 'customer':
                            // Customer name A-Z (cell 4 after adding new columns)
                            aVal = a.cells[4]?.textContent.trim().toLowerCase() || '';
                            bVal = b.cells[4]?.textContent.trim().toLowerCase() || '';
                            return aVal.localeCompare(bVal);

                        case 'recent_comments':
                            // Most recent comments first (invoices without comments go last)
                            aVal = a.dataset.latestComment || '';
                            bVal = b.dataset.latestComment || '';

                            // If both have comments, sort by timestamp (newest first)
                            if (aVal && bVal) {
                                return bVal.localeCompare(aVal);
                            }
                            // Invoices with comments come before those without
                            if (aVal && !bVal) return -1;
                            if (!aVal && bVal) return 1;
                            return 0;

                        default:
                            return 0;
                    }
                });

                // Reorder rows
                rows.forEach(row => tbody.appendChild(row));
            });
        }

        // Toggle Bulk Selection Mode
        function toggleBulkMode() {
            const bulkColumns = document.querySelectorAll('.bulk-column');
            const bulkPanel = document.getElementById('bulkActionsPanel');
            const bulkBtn = document.getElementById('bulkModeBtn');

            const isHidden = bulkColumns[0].style.display === 'none';

            bulkColumns.forEach(col => {
                col.style.display = isHidden ? 'table-cell' : 'none';
            });

            bulkPanel.classList.toggle('hidden');

            if (isHidden) {
                bulkBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                bulkBtn.classList.add('bg-purple-800');
            } else {
                bulkBtn.classList.remove('bg-purple-800');
                bulkBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
                // Clear all checkboxes when exiting bulk mode
                deselectAll();
            }
        }

        // Update Selected Count
        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkedBoxes.length;
        }

        // Select All Visible Invoices
        function selectAll() {
            const checkboxes = document.querySelectorAll('.invoice-checkbox');
            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = true;
                }
            });
            updateSelectedCount();
        }

        // Deselect All Invoices
        function deselectAll() {
            const checkboxes = document.querySelectorAll('.invoice-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
        }

        // Send Bulk Reminders
        async function sendBulkReminders() {
            const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('{{ __("dashboard.no_invoices_selected") }}');
                return;
            }

            if (!confirm(`Send reminders for ${checkedBoxes.length} invoices?`)) {
                return;
            }

            const invoices = Array.from(checkedBoxes).map(cb => ({
                invoice_number: cb.dataset.invoice,
                customer_number: cb.dataset.customer,
            }));

            let successCount = 0;
            let failCount = 0;

            for (const invoice of invoices) {
                try {
                    const response = await fetch('{{ route("reminders.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(invoice),
                    });

                    const data = await response.json();
                    if (data.success) {
                        successCount++;
                    } else {
                        failCount++;
                    }
                } catch (error) {
                    failCount++;
                }
            }

            alert(`{{ __("dashboard.bulk_reminder_sent") }}\nSuccess: ${successCount}\nFailed: ${failCount}`);
            deselectAll();
        }

        // Select All Checkbox Handler
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckboxes = document.querySelectorAll('.select-all-checkbox');
            selectAllCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const section = this.closest('[data-employee-section]');
                    const invoiceCheckboxes = section.querySelectorAll('.invoice-checkbox');
                    invoiceCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateSelectedCount();
                });
            });

            // Restore UI preferences from localStorage
            restorePreferences();

            // Save preferences when filters change
            document.getElementById('employeeFilter')?.addEventListener('change', savePreferences);
            document.getElementById('sortBy')?.addEventListener('change', savePreferences);
        });

        // Save UI Preferences to localStorage
        function savePreferences() {
            const preferences = {
                employeeFilter: document.getElementById('employeeFilter')?.value || '',
                sortBy: document.getElementById('sortBy')?.value || '',
                searchTerm: document.getElementById('searchInput')?.value || '',
                savedAt: new Date().toISOString()
            };
            localStorage.setItem('dashboard_preferences', JSON.stringify(preferences));
        }

        // Restore UI Preferences from localStorage
        function restorePreferences() {
            const saved = localStorage.getItem('dashboard_preferences');
            if (!saved) return;

            try {
                const preferences = JSON.parse(saved);

                // Restore employee filter
                if (preferences.employeeFilter && document.getElementById('employeeFilter')) {
                    document.getElementById('employeeFilter').value = preferences.employeeFilter;
                    filterByEmployee(preferences.employeeFilter);
                }

                // Restore sort order
                if (preferences.sortBy && document.getElementById('sortBy')) {
                    document.getElementById('sortBy').value = preferences.sortBy;
                    sortInvoices(preferences.sortBy);
                }

                // Restore search term
                if (preferences.searchTerm && document.getElementById('searchInput')) {
                    document.getElementById('searchInput').value = preferences.searchTerm;
                    searchInvoices();
                }
            } catch (error) {
                console.error('Error restoring preferences:', error);
            }
        }

        // Clear saved preferences
        function clearPreferences() {
            localStorage.removeItem('dashboard_preferences');
            location.reload();
        }

        // NEW: Sync Now functionality with real-time progress
        let isSyncing = false;
        let progressInterval = null;

        function syncNow() {
            if (isSyncing) {
                return;
            }

            if (!confirm('This will sync all invoices from E-conomic (may take 30-60 seconds). Continue?')) {
                return;
            }

            isSyncing = true;
            const button = document.getElementById('syncButton');
            const buttonText = document.getElementById('syncButtonText');
            const syncIcon = document.getElementById('syncIcon');
            const progressDiv = document.getElementById('syncProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            // Update button state
            button.disabled = true;
            buttonText.textContent = 'Initializing...';
            syncIcon.classList.add('animate-spin');

            // Show progress bar
            progressDiv.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressText.textContent = 'Starting sync...';

            // Start polling for progress
            progressInterval = setInterval(pollProgress, 1000); // Poll every second

            // Make sync request
            fetch('{{ route('dashboard.sync') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);

                if (data.success) {
                    // Update to 100%
                    progressBar.style.width = '100%';
                    progressText.textContent = `Completed! Fetched ${data.stats.total_fetched.toLocaleString()} invoices`;

                    setTimeout(() => {
                        alert(`Sync completed successfully!\n\n` +
                              `Fetched: ${data.stats.total_fetched.toLocaleString()} invoices\n` +
                              `Created: ${data.stats.total_created.toLocaleString()} new\n` +
                              `Updated: ${data.stats.total_updated.toLocaleString()} existing\n` +
                              `Duration: ${data.stats.duration_seconds} seconds`);

                        // Reload page to show updated data
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Sync failed: ' + data.message);
                    resetButton();
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                console.error('Sync error:', error);
                alert('Sync failed: ' + error.message);
                resetButton();
            });
        }

        function pollProgress() {
            fetch('{{ route('dashboard.sync-progress') }}')
                .then(response => response.json())
                .then(data => {
                    const progressBar = document.getElementById('progressBar');
                    const progressText = document.getElementById('progressText');
                    const buttonText = document.getElementById('syncButtonText');

                    if (data.status === 'running') {
                        progressBar.style.width = data.percentage + '%';
                        progressText.textContent = `${data.percentage}% - ${data.message}`;
                        buttonText.textContent = `Syncing ${data.percentage.toFixed(0)}%`;
                    } else if (data.status === 'completed') {
                        progressBar.style.width = '100%';
                        progressText.textContent = 'Completed!';
                    }
                })
                .catch(error => {
                    console.error('Progress poll error:', error);
                });
        }

        function resetButton() {
            isSyncing = false;
            const button = document.getElementById('syncButton');
            const buttonText = document.getElementById('syncButtonText');
            const syncIcon = document.getElementById('syncIcon');
            const progressDiv = document.getElementById('syncProgress');

            button.disabled = false;
            buttonText.textContent = 'Sync Now';
            syncIcon.classList.remove('animate-spin');
            progressDiv.classList.add('hidden');

            if (progressInterval) {
                clearInterval(progressInterval);
            }
        }

        // Comments functionality
        const commentsCache = {};
        const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

        async function toggleComments(invoiceId) {
            if (!invoiceId) return;

            const panel = document.getElementById(`comments-row-${invoiceId}`);
            if (!panel) return;

            // If this panel is already open, close it
            if (!panel.classList.contains('hidden')) {
                closeCommentsPanel(panel);
                return;
            }

            // Close all other open comment panels first
            const allPanels = document.querySelectorAll('.comments-row:not(.hidden)');
            allPanels.forEach(otherPanel => {
                if (otherPanel.id !== `comments-row-${invoiceId}`) {
                    closeCommentsPanel(otherPanel);
                }
            });

            // Show panel with slide down animation
            panel.classList.remove('hidden');
            panel.style.maxHeight = '0';
            panel.style.overflow = 'hidden';
            panel.style.transition = 'max-height 0.3s ease-in';

            // Load comments if not cached
            if (!commentsCache[invoiceId] ||
                (Date.now() - (commentsCache[invoiceId].timestamp || 0)) > CACHE_DURATION) {
                await loadComments(invoiceId);
            } else {
                renderComments(invoiceId, commentsCache[invoiceId].data);
            }

            setTimeout(() => {
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }, 10);

            // After panel starts expanding, scroll to show it at the top
            setTimeout(() => {
                const invoiceRow = panel.previousElementSibling;
                if (invoiceRow) {
                    // Scroll with offset to account for fixed headers
                    const yOffset = -20; // 20px from top
                    const y = invoiceRow.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({ top: y, behavior: 'smooth' });
                }
            }, 50);

            setTimeout(() => {
                panel.style.maxHeight = '';
                panel.style.overflow = '';
                panel.style.transition = '';
            }, 310);
        }

        function closeCommentsPanel(panel) {
            if (!panel || panel.classList.contains('hidden')) return;

            panel.style.maxHeight = panel.scrollHeight + 'px';
            setTimeout(() => {
                panel.style.maxHeight = '0';
                panel.style.overflow = 'hidden';
                panel.style.transition = 'max-height 0.3s ease-out';
            }, 10);
            setTimeout(() => {
                panel.classList.add('hidden');
                panel.style.maxHeight = '';
                panel.style.overflow = '';
                panel.style.transition = '';
            }, 310);
        }

        async function loadComments(invoiceId) {
            const loadingDiv = document.getElementById(`loading-${invoiceId}`);
            const listDiv = document.getElementById(`comments-list-${invoiceId}`);
            const formDiv = document.getElementById(`add-comment-${invoiceId}`);

            if (loadingDiv) loadingDiv.classList.remove('hidden');
            if (listDiv) listDiv.classList.add('hidden');
            if (formDiv) formDiv.classList.add('hidden');

            try {
                const response = await fetch(`/api/invoices/${invoiceId}/comments`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Failed to load comments');

                const comments = await response.json();

                // Cache the results
                commentsCache[invoiceId] = {
                    data: comments,
                    timestamp: Date.now()
                };

                renderComments(invoiceId, comments);
            } catch (error) {
                console.error('Error loading comments:', error);
                if (listDiv) {
                    listDiv.innerHTML = '<p class="text-red-600 dark:text-red-400">{{ __('dashboard.failed_load_comments') }}</p>';
                    listDiv.classList.remove('hidden');
                }
            } finally {
                if (loadingDiv) loadingDiv.classList.add('hidden');
                if (formDiv) formDiv.classList.remove('hidden');
            }
        }

        function renderComments(invoiceId, comments) {
            const listDiv = document.getElementById(`comments-list-${invoiceId}`);
            const addSection = document.getElementById(`add-comment-section-${invoiceId}`);
            if (!listDiv) return;

            if (comments.length === 0) {
                listDiv.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-sm italic">{{ __('dashboard.no_comments_yet') }}</p>';
            } else {
                // Sticky note colors for variety
                const stickyColors = [
                    'bg-yellow-200 dark:bg-yellow-600',
                    'bg-pink-200 dark:bg-pink-600',
                    'bg-blue-200 dark:bg-blue-600',
                    'bg-green-200 dark:bg-green-600',
                    'bg-purple-200 dark:bg-purple-600'
                ];

                const commentsHtml = comments.map((comment, index) => {
                    const date = new Date(comment.created_at);
                    const formattedDate = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short'}) + ' ' +
                                        date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

                    const colorClass = stickyColors[index % stickyColors.length];
                    const rotation = (index % 3 === 0) ? '-rotate-1' : ((index % 3 === 1) ? 'rotate-1' : '');

                    return `
                        <div class="sticky-note ${colorClass} ${rotation} p-3 rounded shadow-md transform hover:scale-105 transition-transform duration-200">
                            <div class="flex items-start justify-between mb-2">
                                <div class="text-xs font-bold text-gray-800 dark:text-gray-900">
                                    ${escapeHtml(comment.user?.name || '{{ __('dashboard.unknown_user') }}')}
                                </div>
                                <div class="text-[10px] text-gray-600 dark:text-gray-800 opacity-75">
                                    ${formattedDate}
                                </div>
                            </div>
                            <div class="text-sm text-gray-800 dark:text-gray-900 leading-snug">
                                ${formatCommentText(escapeHtml(comment.comment))}
                            </div>
                        </div>
                    `;
                }).join('');

                listDiv.innerHTML = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">${commentsHtml}</div>`;
            }

            listDiv.classList.remove('hidden');
            if (addSection) addSection.classList.remove('hidden');
        }

        function showAddForm(invoiceId) {
            const addBtn = document.getElementById(`add-btn-${invoiceId}`);
            const addForm = document.getElementById(`add-form-${invoiceId}`);

            if (addBtn) addBtn.classList.add('hidden');
            if (addForm) addForm.classList.remove('hidden');

            // Focus textarea
            const textarea = document.getElementById(`comment-input-${invoiceId}`);
            if (textarea) textarea.focus();
        }

        function cancelAddComment(invoiceId) {
            const addBtn = document.getElementById(`add-btn-${invoiceId}`);
            const addForm = document.getElementById(`add-form-${invoiceId}`);
            const textarea = document.getElementById(`comment-input-${invoiceId}`);

            if (addForm) addForm.classList.add('hidden');
            if (addBtn) addBtn.classList.remove('hidden');
            if (textarea) {
                textarea.value = '';
                updateCharCount(invoiceId);
            }
        }

        async function saveComment(invoiceId) {
            if (!invoiceId) return;

            const textarea = document.getElementById(`comment-input-${invoiceId}`);
            const button = event.target;

            if (!textarea) return;

            const comment = textarea.value.trim();
            if (!comment) {
                alert('{{ __('dashboard.enter_comment') }}');
                return;
            }

            // Disable button and show loading state
            button.disabled = true;
            const originalText = button.innerText;
            button.innerText = '{{ __('dashboard.saving') }}';

            try {
                const response = await fetch(`/api/invoices/${invoiceId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ comment })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to save comment');
                }

                const newComment = await response.json();

                // Clear cache and reload comments
                delete commentsCache[invoiceId];
                await loadComments(invoiceId);

                // Hide add form and show add button again
                cancelAddComment(invoiceId);

                // Update comment count badge with pulse animation
                updateCommentCountBadge(invoiceId, true);

            } catch (error) {
                console.error('Error saving comment:', error);
                alert('Failed to save comment: ' + error.message);
            } finally {
                button.disabled = false;
                button.innerText = originalText;
            }
        }

        function updateCharCount(invoiceId) {
            const textarea = document.getElementById(`comment-input-${invoiceId}`);
            const countSpan = document.getElementById(`char-count-${invoiceId}`);

            if (textarea && countSpan) {
                const count = textarea.value.length;
                countSpan.textContent = `${count}/1000`;

                if (count > 900) {
                    countSpan.classList.add('text-yellow-600', 'dark:text-yellow-400');
                    countSpan.classList.remove('text-gray-500', 'dark:text-gray-400');
                } else {
                    countSpan.classList.remove('text-yellow-600', 'dark:text-yellow-400');
                    countSpan.classList.add('text-gray-500', 'dark:text-gray-400');
                }
            }
        }

        function insertMarkdown(invoiceId, prefix, suffix, placeholder) {
            const textarea = document.getElementById(`comment-input-${invoiceId}`);
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const textToInsert = selectedText || placeholder;

            const before = textarea.value.substring(0, start);
            const after = textarea.value.substring(end);

            textarea.value = before + prefix + textToInsert + suffix + after;

            // Set cursor position
            const newCursorPos = start + prefix.length + textToInsert.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
            textarea.focus();

            updateCharCount(invoiceId);
        }

        function formatCommentText(text) {
            if (!text) return '';

            // Convert markdown to HTML
            let html = text
                // Bold: **text** -> <strong>text</strong>
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                // Italic: _text_ -> <em>text</em>
                .replace(/\_(.+?)\_/g, '<em>$1</em>')
                // Code: `text` -> <code>text</code>
                .replace(/`(.+?)`/g, '<code class="px-1 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">$1</code>')
                // Links: [text](url) -> <a href="url">text</a>
                .replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank" class="text-blue-600 dark:text-blue-400 underline hover:text-blue-800">$1</a>')
                // Bullet lists: - item -> <li>item</li>
                .replace(/^- (.+)$/gm, '<li class="ml-4">• $1</li>')
                // Line breaks
                .replace(/\n/g, '<br>');

            return html;
        }

        function updateCommentCountBadge(invoiceId, triggerPulse = false) {
            // Find the comments button for this invoice
            const buttons = document.querySelectorAll('button[onclick^="toggleComments"]');
            buttons.forEach(button => {
                if (button.getAttribute('onclick').includes(invoiceId)) {
                    // Get current count from cache
                    const count = commentsCache[invoiceId]?.data?.length || 0;

                    // Find or create badge
                    let badge = button.querySelector('span.bg-blue-600');

                    if (count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold text-white bg-blue-600 rounded-full';
                            button.appendChild(badge);
                        }
                        badge.textContent = count;

                        // Trigger pulse animation when a new comment is added
                        if (triggerPulse) {
                            badge.classList.remove('pulse-badge');
                            // Force reflow to restart animation
                            void badge.offsetWidth;
                            badge.classList.add('pulse-badge');

                            // Remove class after animation completes
                            setTimeout(() => {
                                badge.classList.remove('pulse-badge');
                            }, 1800); // 0.6s * 3 iterations = 1.8s
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Initialize Flatpickr for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Date From picker
            flatpickr("#dateFrom", {
                dateFormat: "Y-m-d",
                defaultDate: "{{ $dateFrom ?? '' }}",
                maxDate: "today",
                onChange: function(selectedDates, dateStr, instance) {
                    // Update the "dateTo" minDate when "dateFrom" changes
                    const dateToInstance = document.querySelector("#dateTo")._flatpickr;
                    if (dateToInstance) {
                        dateToInstance.set('minDate', dateStr);
                    }
                }
            });

            // Date To picker
            flatpickr("#dateTo", {
                dateFormat: "Y-m-d",
                defaultDate: "{{ $dateTo ?? '' }}",
                maxDate: "today",
                minDate: "{{ $dateFrom ?? '' }}"
            });
        });
    </script>
</x-app-layout>
