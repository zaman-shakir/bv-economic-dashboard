@forelse($invoicesByEmployee as $employeeData)
    @php
        $criticalInvoices = collect($employeeData['invoices'])->filter(fn($inv) => $inv['daysOverdue'] > 30)->count();
    @endphp
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6" data-employee-section="{{ $employeeData['employeeNumber'] }}">
        <!-- Employee Header (Clickable to collapse) -->
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition"
             onclick="toggleSection({{ $employeeData['employeeNumber'] }})">
            <div class="flex justify-between items-center">
                <div class="flex-1 flex items-center gap-3">
                    <!-- Collapse Icon -->
                    <svg id="icon-{{ $employeeData['employeeNumber'] }}" class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform duration-200" style="transform: rotate(180deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            {{ $employeeData['employeeName'] }}
                        </h2>
                        <div class="flex items-center gap-3 mt-1">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $employeeData['invoiceCount'] }} {{ $employeeData['invoiceCount'] === 1 ? __('dashboard.invoice') : __('dashboard.invoices') }}
                            </p>
                            @if($criticalInvoices > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400">
                                    🚨 {{ $criticalInvoices }} {{ __('dashboard.critical') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if($employeeData['totalRemainder'] > 0 && ($currentFilter ?? 'overdue') === 'overdue')
                        <button
                            onclick="event.stopPropagation(); sendEmployeeReminder({{ $employeeData['employeeNumber'] }}, this)"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                            title="{{ __('dashboard.send_employee_reminder') }}">
                            🔔 {{ __('dashboard.send_employee_reminder') }}
                        </button>
                    @endif
                    <div class="text-right">
                        <p class="text-lg font-bold {{ $employeeData['totalRemainder'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($employeeData['totalRemainder'], 2, ',', '.') }} DKK
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.outstanding') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsible Invoice Content -->
        <div id="employee-{{ $employeeData['employeeNumber'] }}" style="display: block;">

        <!-- Invoices Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-3 py-3 text-center font-medium bulk-column" style="display:none;">
                            <input type="checkbox" class="select-all-checkbox rounded border-gray-300 dark:border-gray-600">
                        </th>
                        <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.customer_number') }}</th>
                        <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.customer_name') }}</th>
                        <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.subject') }}</th>
                        <th class="px-6 py-3 text-right font-medium">{{ __('dashboard.amount') }}</th>
                        <th class="px-6 py-3 text-right font-medium">{{ __('dashboard.outstanding') }}</th>
                        <th class="px-6 py-3 text-center font-medium">{{ __('dashboard.status') }}</th>
                        <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.external_id') }}</th>
                        <th class="px-6 py-3 text-center font-medium">{{ __('dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($employeeData['invoices'] as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50
                            {{ $invoice['status'] === 'overdue' && $invoice['daysOverdue'] > 30 ? 'bg-red-50 dark:bg-red-900/20' :
                               ($invoice['status'] === 'overdue' && $invoice['daysOverdue'] > 14 ? 'bg-yellow-50 dark:bg-yellow-900/20' :
                               ($invoice['status'] === 'paid' ? 'bg-green-50 dark:bg-green-900/20' : '')) }}">
                            <td class="px-3 py-4 text-center bulk-column" style="display:none;">
                                <input type="checkbox"
                                       class="invoice-checkbox rounded border-gray-300 dark:border-gray-600"
                                       data-invoice="{{ $invoice['invoiceNumber'] }}"
                                       data-customer="{{ $invoice['kundenr'] }}"
                                       data-employee="{{ $employeeData['employeeNumber'] }}"
                                       onchange="updateSelectedCount()">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ $invoice['kundenr'] }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $invoice['kundenavn'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ Str::limit($invoice['overskrift'], 40) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ number_format($invoice['beloeb'], 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold {{ $invoice['remainder'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ number_format($invoice['remainder'], 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($invoice['status'] === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                        {{ __('dashboard.status_paid') }}
                                    </span>
                                @elseif($invoice['status'] === 'overdue')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $invoice['daysOverdue'] > 30 ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400' :
                                           ($invoice['daysOverdue'] > 14 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400' : 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-400') }}">
                                        {{ $invoice['daysOverdue'] }} {{ $invoice['daysOverdue'] === 1 ? __('dashboard.day_overdue') : __('dashboard.days_overdue') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                        {{ $invoice['daysTillDue'] }} {{ $invoice['daysTillDue'] === 1 ? __('dashboard.day_remaining') : __('dashboard.days_remaining') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($invoice['eksterntId'])
                                    <span class="font-mono text-xs">{{ $invoice['eksterntId'] }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($invoice['status'] !== 'paid')
                                    <button
                                        onclick="sendReminder({{ $invoice['invoiceNumber'] }}, {{ $invoice['kundenr'] }}, this)"
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="{{ __('dashboard.send_reminder') }}">
                                        📧 {{ __('dashboard.send_reminder') }}
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div><!-- Close collapsible content -->
    </div>
@empty
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-8 text-center">
        <span class="text-4xl">🎉</span>
        <h3 class="mt-2 text-lg font-medium text-green-800 dark:text-green-400">{{ __('dashboard.no_overdue_invoices') }}</h3>
        <p class="text-green-600 dark:text-green-500">{{ __('dashboard.all_invoices_paid') }}</p>
    </div>
@endforelse
