<x-app-layout>

    <!-- HTMX Script -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <div class="pt-6 pb-12">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            <!-- Top Toolbar: Filters + Employee Filter + Refresh -->
            <div class="mb-6 flex flex-wrap items-center gap-3">
                <!-- Filter Buttons -->
                <div class="flex gap-2">
                    <a href="{{ route('dashboard', ['filter' => 'all']) }}"
                       class="px-5 py-2.5 rounded-xl font-semibold transition-all duration-200 {{ $currentFilter === 'all' ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-elevation-2 btn-lift' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm hover:shadow-md' }}">
                        {{ __('dashboard.filter_all') }}
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'overdue']) }}"
                       class="px-5 py-2.5 rounded-xl font-semibold transition-all duration-200 {{ $currentFilter === 'overdue' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-elevation-2 btn-lift' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm hover:shadow-md' }}">
                        {{ __('dashboard.filter_overdue') }}
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'unpaid']) }}"
                       class="px-5 py-2.5 rounded-xl font-semibold transition-all duration-200 {{ $currentFilter === 'unpaid' ? 'bg-gradient-to-r from-yellow-600 to-yellow-700 text-white shadow-elevation-2 btn-lift' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm hover:shadow-md' }}">
                        {{ __('dashboard.filter_unpaid') }}
                    </a>
                </div>

                <!-- Employee Filter -->
                <div class="flex-1 min-w-[200px]">
                    <select id="employeeFilter" onchange="filterByEmployee(this.value)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-200 shadow-sm hover:shadow-md">
                        <option value="">{{ __('dashboard.all_employees') }}</option>
                        @foreach($invoicesByEmployee as $emp)
                            <option value="{{ $emp['employeeNumber'] }}">
                                {{ $emp['employeeName'] }} ({{ $emp['invoiceCount'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Refresh Button -->
                <button
                    hx-get="{{ route('dashboard.refresh', ['filter' => $currentFilter]) }}"
                    hx-target="#invoice-list"
                    hx-swap="innerHTML"
                    hx-indicator="#loading"
                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 shadow-elevation-2 btn-lift"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('dashboard.refresh_data') }}
                </button>
                <div id="loading" class="htmx-indicator text-sm text-blue-600 dark:text-blue-400 font-medium">
                    {{ __('dashboard.loading_data') }}
                </div>

                <!-- NEW: Sync Now Button -->
                @if($usingDatabase ?? false)
                <div class="flex flex-col gap-2 min-w-[200px]">
                    <button
                        id="syncButton"
                        onclick="syncNow()"
                        class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg id="syncIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span id="syncButtonText">Sync Now</span>
                    </button>

                    <!-- Progress Bar (hidden by default) -->
                    <div id="syncProgress" class="hidden w-full">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div id="progressBar" class="bg-purple-500 h-2.5 transition-all duration-300 rounded-full" style="width: 0%"></div>
                        </div>
                        <div id="progressText" class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium"></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Combined Info Banners (Side by Side) -->
            @if($usingDatabase ?? false)
            <div class="mb-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
                <!-- Sync Status (Left) -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-700 border border-green-200 dark:border-green-800 rounded-lg p-3 shadow-sm">
                    <div class="flex items-center gap-3 flex-wrap text-sm text-gray-600 dark:text-gray-400">
                        @if($lastSyncedAt && $lastSyncedAt->diffInMinutes(now()) < 30)
                            <span class="flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            <span class="font-medium text-green-700 dark:text-green-400">{{ __('dashboard.data_up_to_date') }}</span>
                        @else
                            <span class="flex h-3 w-3 rounded-full bg-yellow-500"></span>
                            <span class="font-medium text-yellow-700 dark:text-yellow-400">Sync recommended</span>
                        @endif
                        <span>•</span>
                        @if($lastSyncedAt)
                            <span>Last: <strong>{{ $lastSyncedAt->diffForHumans() }}</strong> <span class="text-xs">({{ $lastSyncedAt->format('d M H:i') }})</span></span>
                        @else
                            <span><strong>Never synced</strong></span>
                        @endif
                        <span>•</span>
                        <span>DB: <strong>{{ number_format($syncStats['total_invoices'] ?? 0) }}</strong></span>
                        @if($nextSyncAt)
                        <span>•</span>
                        @if($nextSyncAt->isPast())
                            <span>Next: <strong class="text-yellow-600 dark:text-yellow-400">Overdue</strong></span>
                        @else
                            <span>Next: <strong>{{ $nextSyncAt->diffForHumans() }}</strong> <span class="text-xs">({{ $nextSyncAt->format('H:i') }})</span></span>
                        @endif
                        @endif
                    </div>
                </div>

                <!-- Data View Info (Right) -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg p-3 shadow-sm">
                    <div class="flex flex-col gap-1 text-sm text-gray-700 dark:text-gray-300">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">📊</span>
                            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded text-xs font-medium">Database</span>
                            <span>•</span>
                            <span>All <strong class="text-blue-700 dark:text-blue-400">{{ number_format($syncStats['total_invoices'] ?? 0) }}</strong></span>
                            <span>•</span>
                            <span>Filter: <strong class="text-blue-700 dark:text-blue-400">
                                @if($currentFilter === 'all')All
                                @elseif($currentFilter === 'overdue')Overdue
                                @elseif($currentFilter === 'unpaid')Unpaid
                                @endif
                            </strong></span>
                            <span>•</span>
                            <span>Showing: <strong class="text-blue-700 dark:text-blue-400">{{ $invoicesByEmployee->sum('invoiceCount') }}</strong></span>
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
            <div class="mb-4 flex flex-wrap items-center gap-3 card-glass p-4">
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

                <!-- Date Range Filter -->
                <div class="flex items-center gap-2">
                    <input type="date" id="dateFrom" value="{{ $dateFrom ?? '' }}"
                           class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">to</span>
                    <input type="date" id="dateTo" value="{{ $dateTo ?? '' }}"
                           class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
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
                </select>

                <!-- Bulk Actions -->
                <button onclick="toggleBulkMode()" id="bulkModeBtn"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ __('dashboard.bulk_actions') }}
                </button>

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
    </script>
</x-app-layout>
