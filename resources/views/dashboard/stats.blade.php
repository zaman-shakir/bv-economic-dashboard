<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('dashboard.stats') }}
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('dashboard.last_updated') }}: {{ $lastUpdated }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Selection -->
            <div class="mb-6 flex gap-2">
                <a href="{{ route('dashboard.stats', ['filter' => 'all']) }}"
                   class="px-4 py-2 rounded-lg font-medium transition {{ $currentFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {{ __('dashboard.filter_all') }}
                </a>
                <a href="{{ route('dashboard.stats', ['filter' => 'overdue']) }}"
                   class="px-4 py-2 rounded-lg font-medium transition {{ $currentFilter === 'overdue' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {{ __('dashboard.filter_overdue') }}
                </a>
                <a href="{{ route('dashboard.stats', ['filter' => 'unpaid']) }}"
                   class="px-4 py-2 rounded-lg font-medium transition {{ $currentFilter === 'unpaid' ? 'bg-yellow-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    {{ __('dashboard.filter_unpaid') }}
                </a>
            </div>

            <!-- Data Info Banner -->
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg px-4 py-3 shadow-sm">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="font-semibold text-gray-900 dark:text-gray-100">📊 Data Source:</span>

                        @if($usingDatabase ?? false)
                            <!-- Database Mode -->
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-md font-medium">Database (All Invoices)</span>
                            <span>•</span>
                            <span>Total: <strong class="text-blue-700 dark:text-blue-400">{{ number_format($syncStats['total_invoices'] ?? 0) }}</strong> invoices</span>
                        @else
                            <!-- API Mode (Fallback) -->
                            <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-md font-medium">Live API (Limited)</span>
                            <span>•</span>
                            <span class="text-yellow-700 dark:text-yellow-400">⚠️ Showing only last {{ config('e-conomic.sync_months', 6) }} months</span>
                        @endif

                        <span>•</span>
                        <span>Filter: <strong class="text-blue-700 dark:text-blue-400">
                            @if($currentFilter === 'all')All Invoices
                            @elseif($currentFilter === 'overdue')Overdue Only
                            @elseif($currentFilter === 'unpaid')Unpaid Only
                            @endif
                        </strong></span>
                        <span>•</span>
                        <span>Showing: <strong class="text-blue-700 dark:text-blue-400">{{ $invoicesByEmployee->sum('invoiceCount') }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Quick Stats Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        📊 {{ __('dashboard.quick_stats') }}
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if($currentFilter === 'all')
                                    {{ __('dashboard.total_invoices') }}
                                @elseif($currentFilter === 'unpaid')
                                    {{ __('dashboard.unpaid_invoices') }}
                                @else
                                    {{ __('dashboard.overdue_invoices') }}
                                @endif
                            </p>
                            <p class="text-3xl font-bold {{ $currentFilter === 'overdue' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ $invoicesByEmployee->sum('invoiceCount') }}
                            </p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.total_outstanding') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($invoicesByEmployee->sum('totalRemainder'), 2, ',', '.') }} DKK
                            </p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.employees_count') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $invoicesByEmployee->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Critical Invoices Card -->
                @php
                    $criticalCount = 0;
                    $criticalAmount = 0;
                    foreach($invoicesByEmployee as $emp) {
                        foreach($emp['invoices'] as $inv) {
                            if(isset($inv['daysOverdue']) && $inv['daysOverdue'] > 30) {
                                $criticalCount++;
                                $criticalAmount += $inv['remainder'];
                            }
                        }
                    }
                @endphp
                @if($criticalCount > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-400 mb-4">
                            🚨 {{ __('dashboard.critical_invoices') }}
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-red-600 dark:text-red-400">{{ __('dashboard.over_30_days') }}</p>
                                <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                                    {{ $criticalCount }}
                                </p>
                            </div>
                            <div class="border-t border-red-200 dark:border-red-800 pt-3">
                                <p class="text-sm text-red-600 dark:text-red-400">{{ __('dashboard.critical_amount') }}</p>
                                <p class="text-xl font-bold text-red-600 dark:text-red-400">
                                    {{ number_format($criticalAmount, 2, ',', '.') }} DKK
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Top Employees by Outstanding -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        👥 {{ __('dashboard.top_employees') }}
                    </h3>
                    <div class="space-y-3">
                        @foreach($invoicesByEmployee->sortByDesc('totalRemainder')->take(5) as $emp)
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        {{ $emp['employeeName'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $emp['invoiceCount'] }} {{ __('dashboard.invoices') }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-red-600 dark:text-red-400">
                                    {{ number_format($emp['totalRemainder'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        📈 Invoice Status Distribution
                    </h3>
                    <div style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        👥 Top 5 Employees by Outstanding
                    </h3>
                    <div style="height: 300px;">
                        <canvas id="employeeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            @php
                $statusCounts = ['overdue' => 0, 'paid' => 0, 'unpaid' => 0];
                foreach($invoicesByEmployee as $emp) {
                    foreach($emp['invoices'] as $inv) {
                        $statusCounts[$inv['status']]++;
                    }
                }

                $topEmployees = $invoicesByEmployee->sortByDesc('totalRemainder')->take(5)->values();
            @endphp

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Overdue', 'Paid', 'Unpaid'],
                        datasets: [{
                            data: [{{ $statusCounts['overdue'] }}, {{ $statusCounts['paid'] }}, {{ $statusCounts['unpaid'] }}],
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(59, 130, 246, 0.8)'
                            ],
                            borderColor: [
                                'rgba(239, 68, 68, 1)',
                                'rgba(34, 197, 94, 1)',
                                'rgba(59, 130, 246, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#374151',
                                    padding: 10
                                }
                            }
                        }
                    }
                });
            }

            // Outstanding Amount by Employee
            const employeeCtx = document.getElementById('employeeChart');
            if (employeeCtx) {
                new Chart(employeeCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            @foreach($topEmployees as $emp)
                                '{{ Str::limit($emp["employeeName"], 15) }}'{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        ],
                        datasets: [{
                            label: 'Outstanding (DKK)',
                            data: [
                                @foreach($topEmployees as $emp)
                                    {{ $emp['totalRemainder'] }}{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            ],
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#6b7280',
                                    callback: function(value) {
                                        return value.toLocaleString('da-DK');
                                    }
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.2)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6b7280'
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.2)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        }
    </script>
</x-app-layout>
