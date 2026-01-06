<x-app-layout>

    <!-- HTMX Script -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <div class="py-12">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">

            <!-- DATA FLOW: Page Title & Overall Flow -->
            <div class="mb-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg p-6 shadow-lg">
                <h1 class="text-3xl font-bold mb-3">📊 Invoice Dashboard - Data Flow Learning Mode</h1>
                <div class="bg-white/10 rounded p-4 text-sm">
                    <div class="font-bold mb-2">🔄 HOW THIS PAGE WORKS:</div>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div class="bg-white/10 rounded p-2">
                            <div class="font-bold">1️⃣ Fetch All</div>
                            <div class="text-xs">Get ALL invoices from E-conomic API</div>
                        </div>
                        <div class="bg-white/10 rounded p-2">
                            <div class="font-bold">2️⃣ Filter</div>
                            <div class="text-xs">Filter by: {{ $currentFilter }}</div>
                        </div>
                        <div class="bg-white/10 rounded p-2">
                            <div class="font-bold">3️⃣ Group</div>
                            <div class="text-xs">Group by salesperson</div>
                        </div>
                        <div class="bg-white/10 rounded p-2">
                            <div class="font-bold">4️⃣ Calculate</div>
                            <div class="text-xs">Days overdue, totals</div>
                        </div>
                        <div class="bg-white/10 rounded p-2">
                            <div class="font-bold">5️⃣ Display</div>
                            <div class="text-xs">Show in tables below</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DATA FLOW: Filter Buttons Explanation -->
            <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                <div class="font-bold text-blue-900 dark:text-blue-300 mb-2">📌 FILTER BUTTONS - How they work:</div>
                <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <div>• Clicking a filter reloads this page with <code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">?filter={{ $currentFilter }}</code> parameter</div>
                    <div>• Current filter: <strong class="text-blue-600">{{ $currentFilter }}</strong></div>
                    <div>• Service method: <code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">getInvoicesByEmployee('{{ $currentFilter }}')</code></div>
                    <div>• Filter logic applied in: <code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">app/Services/EconomicInvoiceService.php:282-286</code></div>
                </div>
            </div>

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

                <!-- DATA FLOW TOOLTIP: Employee Dropdown -->
                <div class="flex-1 min-w-[200px] relative group">
                    <div class="absolute -top-12 left-0 bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 rounded p-2 text-xs hidden group-hover:block z-10 w-96 shadow-lg">
                        <div class="font-bold text-green-900 dark:text-green-300">🔍 EMPLOYEE DROPDOWN</div>
                        <div class="text-gray-700 dark:text-gray-300 mt-1">
                            • Populated by: <code class="bg-green-100 dark:bg-green-800 px-1 rounded">$invoicesByEmployee</code><br/>
                            • Each option = 1 employee group<br/>
                            • Shows: employeeName + invoiceCount<br/>
                            • Client-side filter (no server call)
                        </div>
                    </div>
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
            </div>

            <!-- Second Toolbar: Search, Sort, Export, Bulk Actions -->
            <div class="mb-6 flex flex-wrap items-center gap-3 card-glass p-5">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" id="searchInput" onkeyup="searchInvoices()"
                               placeholder="{{ __('dashboard.search_invoices') }}"
                               class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="flex items-center gap-2">
                    <input type="date" id="dateFrom"
                           class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">to</span>
                    <input type="date" id="dateTo"
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

            <!-- Data Info Banner (Compact) -->
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg px-4 py-3 shadow-sm">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="font-semibold text-gray-900 dark:text-gray-100">📊 Current Data View:</span>
                        <span>Last {{ config('e-conomic.sync_months', 6) }} months ({{ now()->subMonths(config('e-conomic.sync_months', 6))->format('M d, Y') }} - {{ now()->format('M d, Y') }})</span>
                        <span>•</span>
                        <span>Filter: <strong class="text-blue-700 dark:text-blue-400">
                            @if($currentFilter === 'all')All Invoices
                            @elseif($currentFilter === 'overdue')Overdue Only
                            @elseif($currentFilter === 'unpaid')Unpaid Only
                            @endif
                        </strong></span>
                        <span>•</span>
                        <span>Total: <strong class="text-blue-700 dark:text-blue-400">{{ $invoicesByEmployee->sum('invoiceCount') }}</strong></span>
                    </div>
                    @if(isset($dataQuality) && $dataQuality['has_unassigned'])
                        <div class="mt-1 text-yellow-700 dark:text-yellow-400">
                            ⚠️ {{ $dataQuality['message'] }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- DATA FLOW: Complete API to View Mapping -->
            <div class="mb-6 bg-gradient-to-r from-orange-50 to-red-50 dark:from-gray-800 dark:to-gray-900 border-2 border-orange-300 dark:border-orange-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-orange-900 dark:text-orange-300 mb-4">🔄 COMPLETE DATA FLOW - API to View</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- LEFT: API Call -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-orange-200 dark:border-orange-800">
                        <div class="font-bold text-lg mb-3 text-orange-900 dark:text-orange-300">1️⃣ E-CONOMIC API CALL</div>
                        <div class="space-y-2 text-sm">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                                <div class="font-mono text-xs">
                                    <div class="font-bold mb-1">GET https://restapi.e-conomic.com/invoices/booked</div>
                                    <div class="text-gray-600 dark:text-gray-400">?pagesize=1000&filter=date$gte:{{ now()->subMonths(config('e-conomic.sync_months', 6))->format('Y-m-d') }}</div>
                                </div>
                            </div>
                            <div class="font-bold mt-3">Response Example (1 invoice):</div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded font-mono text-xs overflow-x-auto">
<pre>{
  "bookedInvoiceNumber": 10001,
  "date": "2025-11-15",
  "dueDate": "2025-12-01",
  "grossAmount": 25000.00,
  "remainder": 25000.00,
  "currency": "DKK",
  "customer": {
    "customerNumber": 1001
  },
  "recipient": {
    "name": "Restaurant Nordic A/S"
  },
  "references": {
    "salesPerson": {
      "employeeNumber": 3,
      "name": "Jesper Nielsen"
    },
    "other": "WC-2547"
  }
}</pre>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Data Transformation -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-green-200 dark:border-green-800">
                        <div class="font-bold text-lg mb-3 text-green-900 dark:text-green-300">2️⃣ DATA TRANSFORMATION</div>
                        <div class="space-y-2 text-sm">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded">
                                <div class="font-bold">Step 1: Filter</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    @if($currentFilter === 'overdue')
                                        Keep only if: <code class="bg-yellow-100 dark:bg-yellow-800 px-1 rounded">remainder > 0 AND dueDate < today</code>
                                    @elseif($currentFilter === 'unpaid')
                                        Keep only if: <code class="bg-yellow-100 dark:bg-yellow-800 px-1 rounded">remainder > 0</code>
                                    @else
                                        Keep all invoices
                                    @endif
                                </div>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                                <div class="font-bold">Step 2: Group by Employee</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    Group by: <code class="bg-purple-100 dark:bg-purple-800 px-1 rounded">references.salesPerson.employeeNumber</code><br/>
                                    Result: {{ $invoicesByEmployee->count() }} groups
                                </div>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded">
                                <div class="font-bold">Step 3: Calculate Fields</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 space-y-1">
                                    <div>• <strong>daysOverdue</strong> = today - dueDate</div>
                                    <div>• <strong>status</strong> = remainder == 0 ? 'paid' : (overdue ? 'overdue' : 'unpaid')</div>
                                    <div>• <strong>totalRemainder</strong> = sum of all remainder values</div>
                                </div>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                                <div class="font-bold">Step 4: Format & Sort</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    • Format numbers to Danish format<br/>
                                    • Sort by daysOverdue DESC (most critical first)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FIELD MAPPINGS TABLE -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-indigo-200 dark:border-indigo-800">
                    <div class="font-bold text-lg mb-3 text-indigo-900 dark:text-indigo-300">3️⃣ FIELD MAPPINGS - API → Table Columns</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-indigo-100 dark:bg-indigo-900/30">
                                <tr>
                                    <th class="px-3 py-2 text-left">Table Column</th>
                                    <th class="px-3 py-2 text-left">API Field</th>
                                    <th class="px-3 py-2 text-left">Transformation</th>
                                    <th class="px-3 py-2 text-left">Example</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td class="px-3 py-2 font-mono">invoiceNumber</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">bookedInvoiceNumber</td>
                                    <td class="px-3 py-2">Direct mapping</td>
                                    <td class="px-3 py-2">10001</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/30">
                                    <td class="px-3 py-2 font-mono">date</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">date</td>
                                    <td class="px-3 py-2">Format to d.m.y</td>
                                    <td class="px-3 py-2">15.11.25</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono">kundenr</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">customer.customerNumber</td>
                                    <td class="px-3 py-2">Direct mapping</td>
                                    <td class="px-3 py-2">1001</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/30">
                                    <td class="px-3 py-2 font-mono">kundenavn</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">recipient.name</td>
                                    <td class="px-3 py-2">Direct mapping</td>
                                    <td class="px-3 py-2">Restaurant Nordic A/S</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono">overskrift</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">notes.heading</td>
                                    <td class="px-3 py-2">Limit to 40 chars</td>
                                    <td class="px-3 py-2">Order 2547 - Industrial...</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/30">
                                    <td class="px-3 py-2 font-mono">beloeb</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">grossAmount</td>
                                    <td class="px-3 py-2">Format: 25.000,00</td>
                                    <td class="px-3 py-2">25.000,00</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono">remainder</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">remainder</td>
                                    <td class="px-3 py-2">Format: 25.000,00</td>
                                    <td class="px-3 py-2">25.000,00</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/30">
                                    <td class="px-3 py-2 font-mono">eksterntId</td>
                                    <td class="px-3 py-2 font-mono text-blue-600">references.other</td>
                                    <td class="px-3 py-2">Direct mapping</td>
                                    <td class="px-3 py-2">WC-2547</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 font-mono">daysOverdue</td>
                                    <td class="px-3 py-2 font-mono text-green-600">CALCULATED</td>
                                    <td class="px-3 py-2">today - dueDate</td>
                                    <td class="px-3 py-2">36 days</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-900/30">
                                    <td class="px-3 py-2 font-mono">status</td>
                                    <td class="px-3 py-2 font-mono text-green-600">CALCULATED</td>
                                    <td class="px-3 py-2">Based on remainder & dueDate</td>
                                    <td class="px-3 py-2">overdue</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- LIVE DATA EXAMPLE -->
                @if($invoicesByEmployee->count() > 0)
                @php $firstEmployee = $invoicesByEmployee->first(); @endphp
                @if(isset($firstEmployee['invoices']) && count($firstEmployee['invoices']) > 0)
                @php $firstInvoice = $firstEmployee['invoices'][0]; @endphp
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-pink-200 dark:border-pink-800">
                    <div class="font-bold text-lg mb-3 text-pink-900 dark:text-pink-300">4️⃣ LIVE EXAMPLE - First Invoice Data</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="font-bold mb-2">Employee Info:</div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded space-y-1">
                                <div>👤 <strong>Name:</strong> {{ $firstEmployee['employeeName'] }}</div>
                                <div>🔢 <strong>Employee #:</strong> {{ $firstEmployee['employeeNumber'] }}</div>
                                <div>📊 <strong>Invoice Count:</strong> {{ $firstEmployee['invoiceCount'] }}</div>
                                <div>💰 <strong>Total Outstanding:</strong> {{ number_format($firstEmployee['totalRemainder'], 2, ',', '.') }} DKK</div>
                            </div>
                        </div>
                        <div>
                            <div class="font-bold mb-2">First Invoice Details:</div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded space-y-1 font-mono text-xs">
                                <div>📄 <strong>Invoice:</strong> #{{ $firstInvoice['invoiceNumber'] }}</div>
                                <div>🏢 <strong>Customer:</strong> {{ $firstInvoice['kundenavn'] }}</div>
                                <div>💵 <strong>Amount:</strong> {{ number_format($firstInvoice['beloeb'], 2, ',', '.') }} {{ $firstInvoice['currency'] }}</div>
                                <div>⏰ <strong>Days Overdue:</strong> {{ $firstInvoice['daysOverdue'] }}</div>
                                <div>🎯 <strong>Status:</strong> <span class="px-2 py-1 rounded {{ $firstInvoice['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ $firstInvoice['status'] }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                        ℹ️ This is REAL DATA from your database, displayed exactly as it appears in the table below.
                    </div>
                </div>
                @endif
                @endif

                <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                    ⬇️ <strong>Below you'll see the actual invoice table populated with this data</strong> ⬇️
                </div>
            </div>

            <!-- DATA FLOW: Caching Strategy -->
            <div class="mb-6 bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 border-2 border-cyan-300 dark:border-cyan-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-cyan-900 dark:text-cyan-300 mb-4">💾 CACHING STRATEGY - How We Use Cache</h2>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-4 border-l-4 border-cyan-500">
                    <div class="font-bold text-lg mb-2">🎯 Why Cache?</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        E-conomic API calls are slow (500ms-2s each). Without caching, each page load would take 5-10 seconds. With caching, subsequent loads are instant!
                    </div>
                </div>

                <!-- Cache Flow Diagram -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 border-2 border-blue-200 dark:border-blue-800">
                    <div class="font-bold text-lg mb-3 text-blue-900 dark:text-blue-300">🔄 HOW CACHE WORKS</div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- First Request -->
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded border-2 border-red-300 dark:border-red-700">
                            <div class="font-bold text-red-900 dark:text-red-300 mb-2">1️⃣ FIRST REQUEST (Cache MISS)</div>
                            <div class="text-xs space-y-2">
                                <div class="flex items-start gap-2">
                                    <span>❌</span>
                                    <div>Check cache for key: <code class="bg-red-100 dark:bg-red-800 px-1 rounded">overdue_invoices</code></div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>🌐</span>
                                    <div>Cache not found → Make API call to E-conomic</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>⏱️</span>
                                    <div><strong>Takes: 500-2000ms</strong></div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>💾</span>
                                    <div>Store result in cache for 30 minutes</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>📄</span>
                                    <div>Return data to view</div>
                                </div>
                            </div>
                        </div>

                        <!-- Subsequent Requests -->
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded border-2 border-green-300 dark:border-green-700">
                            <div class="font-bold text-green-900 dark:text-green-300 mb-2">2️⃣ NEXT 30 MINUTES (Cache HIT)</div>
                            <div class="text-xs space-y-2">
                                <div class="flex items-start gap-2">
                                    <span>✅</span>
                                    <div>Check cache for key: <code class="bg-green-100 dark:bg-green-800 px-1 rounded">overdue_invoices</code></div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>🎉</span>
                                    <div><strong>Cache found! Use cached data</strong></div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>⚡</span>
                                    <div><strong>Takes: 1-5ms (1000x faster!)</strong></div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>🚫</span>
                                    <div>No API call needed</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>📄</span>
                                    <div>Return cached data to view</div>
                                </div>
                            </div>
                        </div>

                        <!-- After Expiry -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded border-2 border-yellow-300 dark:border-yellow-700">
                            <div class="font-bold text-yellow-900 dark:text-yellow-300 mb-2">3️⃣ AFTER 30 MIN (Refresh)</div>
                            <div class="text-xs space-y-2">
                                <div class="flex items-start gap-2">
                                    <span>⏰</span>
                                    <div>Cache expired (30 min passed)</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>🔄</span>
                                    <div>Cache MISS again</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>🌐</span>
                                    <div>Make fresh API call</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>💾</span>
                                    <div>Store new result for another 30 min</div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>♻️</span>
                                    <div>Cycle repeats</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Keys Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 border-2 border-indigo-200 dark:border-indigo-800">
                    <div class="font-bold text-lg mb-3 text-indigo-900 dark:text-indigo-300">🔑 CACHE KEYS & DURATIONS</div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-indigo-100 dark:bg-indigo-900/30">
                                <tr>
                                    <th class="px-3 py-2 text-left">Cache Key</th>
                                    <th class="px-3 py-2 text-left">Data Stored</th>
                                    <th class="px-3 py-2 text-left">Duration</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Code Location</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2 font-mono text-xs">overdue_invoices</td>
                                    <td class="px-3 py-2">All overdue invoices (remainder > 0 & past due)</td>
                                    <td class="px-3 py-2">{{ config('e-conomic.cache_duration', 30) }} min</td>
                                    <td class="px-3 py-2">
                                        @if(Cache::has('overdue_invoices'))
                                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400 rounded text-xs">✅ HIT</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400 rounded text-xs">❌ MISS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">EconomicInvoiceService.php:203</td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2 font-mono text-xs">unpaid_invoices</td>
                                    <td class="px-3 py-2">All unpaid invoices (remainder > 0)</td>
                                    <td class="px-3 py-2">{{ config('e-conomic.cache_duration', 30) }} min</td>
                                    <td class="px-3 py-2">
                                        @if(Cache::has('unpaid_invoices'))
                                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400 rounded text-xs">✅ HIT</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400 rounded text-xs">❌ MISS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">EconomicInvoiceService.php:231</td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2 font-mono text-xs">all_invoices</td>
                                    <td class="px-3 py-2">All invoices (last {{ config('e-conomic.sync_months', 6) }} months)</td>
                                    <td class="px-3 py-2">{{ config('e-conomic.cache_duration', 30) }} min</td>
                                    <td class="px-3 py-2">
                                        @if(Cache::has('all_invoices'))
                                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400 rounded text-xs">✅ HIT</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400 rounded text-xs">❌ MISS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">EconomicInvoiceService.php:255</td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2 font-mono text-xs">invoice_totals</td>
                                    <td class="px-3 py-2">Overall statistics from E-conomic</td>
                                    <td class="px-3 py-2">5 min</td>
                                    <td class="px-3 py-2">
                                        @if(Cache::has('invoice_totals'))
                                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400 rounded text-xs">✅ HIT</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400 rounded text-xs">❌ MISS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">EconomicInvoiceService.php:398</td>
                                </tr>
                                @if($invoicesByEmployee->count() > 0)
                                    @foreach($invoicesByEmployee->take(3) as $emp)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2 font-mono text-xs">employee_{{ $emp['employeeNumber'] }}</td>
                                        <td class="px-3 py-2">Employee name: {{ $emp['employeeName'] }}</td>
                                        <td class="px-3 py-2">60 min</td>
                                        <td class="px-3 py-2">
                                            @if(Cache::has('employee_' . $emp['employeeNumber']))
                                                <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400 rounded text-xs">✅ HIT</span>
                                            @else
                                                <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400 rounded text-xs">❌ MISS</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 font-mono text-xs">EconomicInvoiceService.php:346</td>
                                    </tr>
                                    @endforeach
                                    @if($invoicesByEmployee->count() > 3)
                                    <tr>
                                        <td colspan="5" class="px-3 py-2 text-center text-xs text-gray-500 dark:text-gray-400">
                                            ... and {{ $invoicesByEmployee->count() - 3 }} more employee cache keys
                                        </td>
                                    </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Current Page Cache Status -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 border-2 border-purple-200 dark:border-purple-800">
                    <div class="font-bold text-lg mb-3 text-purple-900 dark:text-purple-300">📊 CURRENT PAGE CACHE STATUS</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                                <div class="font-bold mb-2">Cache Status for: <code class="bg-purple-100 dark:bg-purple-800 px-2 py-1 rounded">{{ $currentFilter }}_invoices</code></div>
                                <div class="text-sm space-y-2">
                                    @if(Cache::has($currentFilter . '_invoices'))
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">✅</span>
                                            <div>
                                                <div class="font-bold text-green-600 dark:text-green-400">CACHE HIT</div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">Data loaded from cache (fast!)</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">❌</span>
                                            <div>
                                                <div class="font-bold text-red-600 dark:text-red-400">CACHE MISS</div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">Data fetched from E-conomic API (slower)</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                                <div class="font-bold mb-2">How to Clear Cache:</div>
                                <div class="text-sm space-y-2">
                                    <div>1. Click the <strong>"Refresh Data"</strong> button above</div>
                                    <div>2. Or run: <code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-xs">php artisan cache:clear</code></div>
                                    <div>3. Or wait {{ config('e-conomic.cache_duration', 30) }} minutes for auto-expiry</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Code Example -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-gray-200 dark:border-gray-700">
                    <div class="font-bold text-lg mb-3 text-gray-900 dark:text-gray-300">💻 CODE EXAMPLE - How Cache is Used</div>

                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded font-mono text-xs overflow-x-auto">
<pre class="text-gray-800 dark:text-gray-200">// File: app/Services/EconomicInvoiceService.php

public function getOverdueInvoices(): Collection
{
    $cacheDuration = config('e-conomic.cache_duration', 30) * 60; // 30 min → 1800 sec

    return Cache::remember('overdue_invoices', $cacheDuration, function () {
        // This code only runs on CACHE MISS
        $allInvoices = $this->getAllInvoices();

        // Filter to only overdue
        return $allInvoices->filter(function ($invoice) {
            $isDue = $invoice['dueDate'] < now()->format('Y-m-d');
            $hasRemainder = $invoice['remainder'] > 0;
            return $isDue && $hasRemainder;
        });
    });
}</pre>
                    </div>

                    <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                        <strong>How Cache::remember() works:</strong><br/>
                        1. Check if cache key exists<br/>
                        2. If YES (HIT) → Return cached value immediately<br/>
                        3. If NO (MISS) → Execute the function, store result, then return it
                    </div>
                </div>

                <!-- Cache Configuration -->
                <div class="mt-6 bg-gradient-to-r from-orange-50 to-yellow-50 dark:from-gray-900 dark:to-gray-800 rounded-lg p-4 border-2 border-orange-300 dark:border-orange-700">
                    <div class="font-bold text-lg mb-3 text-orange-900 dark:text-orange-300">⚙️ CACHE CONFIGURATION</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="font-bold mb-2">Environment Variables (.env):</div>
                            <div class="bg-white dark:bg-gray-800 p-3 rounded font-mono text-xs space-y-1">
                                <div>ECONOMIC_CACHE_DURATION={{ config('e-conomic.cache_duration', 30) }}</div>
                                <div>CACHE_DRIVER={{ config('cache.default', 'file') }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="font-bold mb-2">Where Cache is Stored:</div>
                            <div class="bg-white dark:bg-gray-800 p-3 rounded text-xs space-y-1">
                                <div><strong>Driver:</strong> {{ config('cache.default', 'file') }}</div>
                                @if(config('cache.default') === 'file')
                                    <div><strong>Location:</strong> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">storage/framework/cache/data/</code></div>
                                @elseif(config('cache.default') === 'redis')
                                    <div><strong>Location:</strong> Redis server</div>
                                @else
                                    <div><strong>Location:</strong> {{ config('cache.default') }} store</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DATA FLOW: On-Demand API Calls -->
            <div class="mb-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-800 dark:to-gray-900 border-2 border-purple-300 dark:border-purple-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-purple-900 dark:text-purple-300 mb-4">📞 ON-DEMAND API CALLS - Customer & Employee Details</h2>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-4 border-l-4 border-purple-500">
                    <div class="font-bold text-lg mb-2">⚡ Important: These APIs are NOT called during page load!</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        The initial invoice data does NOT include customer/employee emails. We only fetch emails when you click the "Send Email" buttons.
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Customer Email API -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
                        <div class="font-bold text-lg mb-3 text-blue-900 dark:text-blue-300">1️⃣ GET CUSTOMER EMAIL</div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded mb-3">
                            <div class="font-bold text-sm mb-2">🔘 TRIGGER:</div>
                            <div class="text-xs">When you click <button class="inline-flex items-center px-2 py-1 text-xs bg-blue-600 text-white rounded">📧 Email</button> on an invoice row</div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded mb-3">
                            <div class="font-bold text-sm mb-2">📡 API CALL:</div>
                            <div class="font-mono text-xs space-y-1">
                                <div class="font-bold">GET https://restapi.e-conomic.com/customers/{customerNumber}</div>
                                <div class="text-gray-600 dark:text-gray-400">Example: GET /customers/1001</div>
                            </div>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded mb-3">
                            <div class="font-bold text-sm mb-2">📦 RESPONSE:</div>
                            <div class="font-mono text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-x-auto">
<pre>{
  "customerNumber": 1001,
  "email": "contact@restaurant-nordic.dk",
  "name": "Restaurant Nordic A/S",
  "address": "Vesterbrogade 123",
  "city": "Copenhagen"
}</pre>
                            </div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                            <div class="font-bold text-sm mb-2">🎯 WHAT WE USE:</div>
                            <div class="text-xs">
                                We extract: <code class="bg-purple-100 dark:bg-purple-800 px-2 py-1 rounded">email</code> field<br/>
                                Then send reminder email to this address
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-gray-600 dark:text-gray-400 border-t pt-2">
                            <strong>Code Location:</strong><br/>
                            <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">app/Http/Controllers/ReminderController.php:139-162</code>
                        </div>
                    </div>

                    <!-- Employee Email API -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-orange-200 dark:border-orange-800">
                        <div class="font-bold text-lg mb-3 text-orange-900 dark:text-orange-300">2️⃣ GET EMPLOYEE EMAIL</div>

                        <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded mb-3">
                            <div class="font-bold text-sm mb-2">🔘 TRIGGER:</div>
                            <div class="text-xs">When you click <button class="inline-flex items-center px-2 py-1 text-xs bg-orange-600 text-white rounded">🔔 Send Email</button> in employee header</div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded mb-3">
                            <div class="font-bold text-sm mb-2">📡 API CALL:</div>
                            <div class="font-mono text-xs space-y-1">
                                <div class="font-bold">GET https://restapi.e-conomic.com/employees/{employeeNumber}</div>
                                <div class="text-gray-600 dark:text-gray-400">Example: GET /employees/3</div>
                            </div>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded mb-3">
                            <div class="font-bold text-sm mb-2">📦 RESPONSE:</div>
                            <div class="font-mono text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-x-auto">
<pre>{
  "employeeNumber": 3,
  "name": "Jesper Nielsen",
  "email": "jesper@billigventilation.dk",
  "employeeGroup": {
    "employeeGroupNumber": 1
  }
}</pre>
                            </div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                            <div class="font-bold text-sm mb-2">🎯 WHAT WE USE:</div>
                            <div class="text-xs">
                                We extract: <code class="bg-purple-100 dark:bg-purple-800 px-2 py-1 rounded">email</code> field<br/>
                                Then send summary email with all overdue invoices
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-gray-600 dark:text-gray-400 border-t pt-2">
                            <strong>Code Location:</strong><br/>
                            <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">app/Http/Controllers/ReminderController.php:235-258</code>
                        </div>
                    </div>
                </div>

                <!-- Employee Name Caching -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-green-200 dark:border-green-800">
                    <div class="font-bold text-lg mb-3 text-green-900 dark:text-green-300">3️⃣ EMPLOYEE NAMES (Cached)</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded">
                                <div class="font-bold mb-2">🔘 WHEN CALLED:</div>
                                <div class="text-xs">During page load, for EACH unique employee number</div>
                            </div>
                        </div>
                        <div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                                <div class="font-bold mb-2">💾 CACHING:</div>
                                <div class="text-xs">Cached for 60 minutes per employee<br/>
                                Cache key: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">employee_{employeeNumber}</code></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded">
                        <div class="font-bold text-sm mb-2">📡 API CALL:</div>
                        <div class="font-mono text-xs">
                            GET https://restapi.e-conomic.com/employees/{employeeNumber}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                            We only use the <code class="bg-yellow-100 dark:bg-yellow-800 px-1 rounded">name</code> field from this response
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-600 dark:text-gray-400 border-t pt-2">
                        <strong>Code Location:</strong> <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">app/Services/EconomicInvoiceService.php:331-356</code>
                    </div>
                </div>

                <!-- Complete API Summary -->
                <div class="mt-6 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 rounded-lg p-4 border-2 border-indigo-300 dark:border-indigo-700">
                    <div class="font-bold text-lg mb-3 text-indigo-900 dark:text-indigo-300">📋 COMPLETE API SUMMARY</div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="bg-white dark:bg-gray-800 rounded p-3">
                            <div class="font-bold text-blue-600 mb-2">ON PAGE LOAD</div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-start gap-2">
                                    <span>1️⃣</span>
                                    <div>
                                        <div class="font-mono">GET /invoices/booked</div>
                                        <div class="text-gray-600 dark:text-gray-400">Fetches all invoices</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>2️⃣</span>
                                    <div>
                                        <div class="font-mono">GET /employees/{id}</div>
                                        <div class="text-gray-600 dark:text-gray-400">For each unique employee (cached 60min)</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span>3️⃣</span>
                                    <div>
                                        <div class="font-mono">GET /invoices/totals</div>
                                        <div class="text-gray-600 dark:text-gray-400">Overall statistics</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded p-3">
                            <div class="font-bold text-green-600 mb-2">ON CUSTOMER REMINDER</div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-start gap-2">
                                    <span>📧</span>
                                    <div>
                                        <div class="font-mono">GET /customers/{id}</div>
                                        <div class="text-gray-600 dark:text-gray-400">Fetch customer email</div>
                                        <div class="text-gray-600 dark:text-gray-400 mt-1">Then send email via Mail facade</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded p-3">
                            <div class="font-bold text-orange-600 mb-2">ON EMPLOYEE REMINDER</div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-start gap-2">
                                    <span>🔔</span>
                                    <div>
                                        <div class="font-mono">GET /employees/{id}</div>
                                        <div class="text-gray-600 dark:text-gray-400">Fetch employee email</div>
                                        <div class="text-gray-600 dark:text-gray-400 mt-1">Then send summary email</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-xs text-center text-gray-600 dark:text-gray-400 bg-white/50 dark:bg-gray-800/50 rounded p-2">
                        💡 <strong>Total API Calls per Page Load:</strong>
                        @if($invoicesByEmployee->count() > 0)
                            1 (invoices) + {{ $invoicesByEmployee->count() }} (employee names) + 1 (totals) = {{ 2 + $invoicesByEmployee->count() }} calls
                        @else
                            Approximately 3-5 calls depending on number of unique employees
                        @endif
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

        // Search Invoices
        function searchInvoices() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const allRows = document.querySelectorAll('tbody tr');

            allRows.forEach(row => {
                // Updated cell indices after adding new columns:
                // 0: Bulk checkbox, 1: Invoice Number, 2: Date, 3: Customer Number, 4: Customer Name, 5: Subject
                const invoiceNumber = row.cells[1]?.textContent.toLowerCase() || '';
                const customerNumber = row.cells[3]?.textContent.toLowerCase() || '';
                const customerName = row.cells[4]?.textContent.toLowerCase() || '';
                const invoiceSubject = row.cells[5]?.textContent.toLowerCase() || '';

                if (invoiceNumber.includes(searchTerm) ||
                    customerNumber.includes(searchTerm) ||
                    customerName.includes(searchTerm) ||
                    invoiceSubject.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Date Range Filter
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

            // Note: This is a client-side filter based on visual data
            // For production, consider server-side filtering for accuracy
            const allSections = document.querySelectorAll('[data-employee-section]');

            allSections.forEach(section => {
                const rows = section.querySelectorAll('tbody tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    // For now, we'll show all rows if dates are selected
                    // In a real implementation, you'd need invoice dates in the data
                    row.style.display = '';
                    visibleCount++;
                });

                // Hide employee section if no invoices match
                if (visibleCount === 0) {
                    section.style.display = 'none';
                } else {
                    section.style.display = 'block';
                }
            });
        }

        // Clear Date Filter
        function clearDateFilter() {
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';

            // Show all rows and sections
            const allRows = document.querySelectorAll('tbody tr');
            const allSections = document.querySelectorAll('[data-employee-section]');

            allRows.forEach(row => row.style.display = '');
            allSections.forEach(section => section.style.display = 'block');
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
    </script>
</x-app-layout>
