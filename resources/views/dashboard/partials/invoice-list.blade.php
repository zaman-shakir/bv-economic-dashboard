@forelse($invoicesByEmployee as $employeeData)
    @php
        $criticalInvoices = collect($employeeData['invoices'])->filter(fn($inv) => $inv['daysOverdue'] > 30)->count();
    @endphp
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6" data-employee-section="{{ $employeeData['employeeNumber'] }}">
        <!-- Employee Header (Clickable to collapse) -->
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition"
             onclick="toggleSection('{{ $employeeData['employeeNumber'] }}')">
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
                    @if($employeeData['totalRemainder'] > 0 && ($currentFilter ?? 'overdue') === 'overdue' && $employeeData['employeeNumber'] !== 'unassigned')
                        <button
                            onclick="event.stopPropagation(); sendEmployeeReminder('{{ $employeeData['employeeNumber'] }}', this)"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group"
                            title="{{ __('dashboard.send_employee_reminder') }}">
                            <svg class="w-4 h-4 group-hover:animate-swing" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span>Send Email</span>
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
                        <th class="px-4 py-3 text-left font-medium">{{ __('dashboard.invoice_number') }}</th>
                        <th class="px-4 py-3 text-left font-medium">{{ __('dashboard.date') }}</th>
                        <th class="px-4 py-3 text-left font-medium">{{ __('dashboard.customer_number') }}</th>
                        <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.customer_name') }}</th>
                        <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.subject') }}</th>
                        <th class="px-4 py-3 text-right font-medium">{{ __('dashboard.amount') }}</th>
                        <th class="px-4 py-3 text-right font-medium">{{ __('dashboard.outstanding') }}</th>
                        <th class="px-4 py-3 text-center font-medium">{{ __('dashboard.status') }}</th>
                        <th class="px-4 py-3 text-left font-medium">{{ __('dashboard.external_id') }}</th>
                        <th class="px-4 py-3 text-left font-medium">External ID</th>
                        <th class="px-4 py-3 text-center font-medium">{{ __('dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($employeeData['invoices'] as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50
                            {{ $invoice['status'] === 'overdue' && $invoice['daysOverdue'] > 30 ? 'bg-red-50 dark:bg-red-900/20' :
                               ($invoice['status'] === 'overdue' && $invoice['daysOverdue'] > 14 ? 'bg-yellow-50 dark:bg-yellow-900/20' :
                               ($invoice['status'] === 'paid' ? 'bg-green-50 dark:bg-green-900/20' : '')) }}"
                            data-latest-comment="{{ $invoice['latestCommentAt'] ?? '' }}">
                            <td class="px-3 py-4 text-center bulk-column" style="display:none;">
                                <input type="checkbox"
                                       class="invoice-checkbox rounded border-gray-300 dark:border-gray-600"
                                       data-invoice="{{ $invoice['invoiceNumber'] }}"
                                       data-customer="{{ $invoice['kundenr'] }}"
                                       data-employee="{{ $employeeData['employeeNumber'] }}"
                                       onchange="updateSelectedCount()">
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex items-center gap-2">
                                    <a href="https://secure.e-conomic.com/secure/include/visfaktura.asp?ops=29217799&bogf=1&faknr={{ $invoice['invoiceNumber'] }}"
                                       target="_blank"
                                       class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline transition"
                                       title="Open invoice in e-conomic">
                                        {{ $invoice['invoiceNumber'] }}
                                    </a>
                                    <a href="https://secure.e-conomic.com/secure/include/visfaktura.asp?ops=29217799&bogf=1&faknr={{ $invoice['invoiceNumber'] }}"
                                       target="_blank"
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition"
                                       title="Open invoice in e-conomic">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($invoice['date'])->format('d.m.y') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ $invoice['kundenr'] }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $invoice['kundenavn'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ Str::limit($invoice['overskrift'], 40) }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ number_format($invoice['beloeb'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-semibold {{ $invoice['remainder'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ number_format($invoice['remainder'], 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($invoice['status'] === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                        {{ __('dashboard.status_paid') }}
                                    </span>
                                @elseif($invoice['status'] === 'overdue')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium
                                        {{ $invoice['daysOverdue'] > 30 ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400' :
                                           ($invoice['daysOverdue'] > 14 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400' : 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-400') }}">
                                        {{ $invoice['daysOverdue'] }} {{ $invoice['daysOverdue'] === 1 ? __('dashboard.day_overdue') : __('dashboard.days_overdue') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                        {{ $invoice['daysTillDue'] }} {{ $invoice['daysTillDue'] === 1 ? __('dashboard.day_remaining') : __('dashboard.days_remaining') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($invoice['eksterntId'])
                                    @php
                                        // Check if it's a WooCommerce order
                                        // BV-WO-xxxxx = BilligVentilation.dk
                                        // BF-WO-xxxxx = BilligFilter.dk
                                        $isBVOrder = preg_match('/BV-WO-(\d+)/i', $invoice['eksterntId'], $bvMatches);
                                        $isBFOrder = preg_match('/BF-WO-(\d+)/i', $invoice['eksterntId'], $bfMatches);

                                        $wooOrderId = null;
                                        $wooSite = null;

                                        if ($isBVOrder) {
                                            $wooOrderId = $bvMatches[1];
                                            $wooSite = 'https://billigventilation.dk';
                                        } elseif ($isBFOrder) {
                                            $wooOrderId = $bfMatches[1];
                                            $wooSite = 'https://billigfilter.dk';
                                        }
                                    @endphp

                                    @if($wooOrderId && $wooSite)
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <a href="{{ $wooSite }}/wp-admin/admin.php?page=wc-orders&action=edit&id={{ $wooOrderId }}"
                                               target="_blank"
                                               class="font-mono text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline transition"
                                               title="{{ __('dashboard.view_woo_order') }}">
                                                {{ $invoice['eksterntId'] }}
                                            </a>
                                            <a href="{{ $wooSite }}/wp-admin/admin.php?page=wc-orders&action=edit&id={{ $wooOrderId }}"
                                               target="_blank"
                                               class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition flex-shrink-0"
                                               title="{{ __('dashboard.view_woo_order') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="font-mono text-xs">{{ $invoice['eksterntId'] }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                @if($invoice['externalId'] ?? null)
                                    <span class="font-mono text-xs">{{ $invoice['externalId'] }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-col items-stretch gap-1.5">
                                    <!-- Comments Button -->
                                    <button
                                        onclick="toggleComments({{ $invoice['invoiceId'] ?? 'null' }})"
                                        class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 transition-all duration-200 relative group focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 dark:focus:ring-offset-gray-800"
                                        title="{{ __('dashboard.view_add_comments') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                        <span>Kommentar</span>
                                        @if(($invoice['commentCount'] ?? 0) > 0)
                                            <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-semibold bg-blue-600 text-white rounded-full">{{ $invoice['commentCount'] }}</span>
                                        @endif
                                    </button>

                                    <!-- Send Reminder Button -->
                                    @if($invoice['status'] !== 'paid')
                                        <button
                                            onclick="sendReminder({{ $invoice['invoiceNumber'] }}, {{ $invoice['kundenr'] }}, this)"
                                            class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm hover:shadow transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800"
                                            title="{{ __('dashboard.send_reminder') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            <span>Send</span>
                                        </button>
                                    @else
                                        <div class="h-7"></div>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <tr id="comments-row-{{ $invoice['invoiceId'] ?? '' }}" class="comments-row hidden">
                            <td colspan="12" class="px-0 py-0 bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="comments-panel">
                                    <!-- Sticky Notes Header -->
                                    <div class="flex justify-between items-center px-6 pt-3 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">📌</span>
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                {{ __('dashboard.invoice_comments') }} - #{{ $invoice['invoiceNumber'] }}
                                            </h3>
                                        </div>
                                        <button
                                            onclick="toggleComments({{ $invoice['invoiceId'] ?? 'null' }})"
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition p-1"
                                            title="{{ __('dashboard.close_comments') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="px-6 pb-4">
                                        <!-- Loading State -->
                                        <div id="loading-{{ $invoice['invoiceId'] ?? '' }}" class="text-center py-4 text-gray-600 dark:text-gray-400">
                                            <svg class="animate-spin h-6 w-6 mx-auto text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="mt-2 text-xs">{{ __('dashboard.loading_comments') }}</p>
                                        </div>

                                        <!-- Sticky Notes Grid (Visible by default) -->
                                        <div id="comments-list-{{ $invoice['invoiceId'] ?? '' }}" class="hidden">
                                            <!-- Sticky notes will be inserted here via JavaScript -->
                                        </div>

                                        <!-- Add New Sticky Note Button & Form -->
                                        <div id="add-comment-section-{{ $invoice['invoiceId'] ?? '' }}" class="hidden mt-3">
                                            <!-- Add button -->
                                            <button
                                                id="add-btn-{{ $invoice['invoiceId'] ?? '' }}"
                                                onclick="showAddForm({{ $invoice['invoiceId'] ?? 'null' }})"
                                                class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded shadow-md transition transform hover:scale-105">
                                                <span class="text-base">📝</span>
                                                {{ __('dashboard.add_comment') }}
                                            </button>

                                            <!-- Add form (hidden initially) -->
                                            <div id="add-form-{{ $invoice['invoiceId'] ?? '' }}" class="hidden mt-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-xl shadow-lg max-w-md">
                                                <!-- Formatting Toolbar -->
                                                <div class="flex items-center gap-1 mb-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                                                    <span class="text-xs text-gray-400 mr-2">Format:</span>
                                                    <button type="button" onclick="insertMarkdown({{ $invoice['invoiceId'] ?? 'null' }}, '**', '**', 'bold text')" class="px-2 py-1 text-xs font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded transition" title="Bold">B</button>
                                                    <button type="button" onclick="insertMarkdown({{ $invoice['invoiceId'] ?? 'null' }}, '_', '_', 'italic text')" class="px-2 py-1 text-xs italic text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded transition" title="Italic">I</button>
                                                    <button type="button" onclick="insertMarkdown({{ $invoice['invoiceId'] ?? 'null' }}, '[', '](url)', 'link text')" class="px-2 py-1 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded transition" title="Link">🔗</button>
                                                    <button type="button" onclick="insertMarkdown({{ $invoice['invoiceId'] ?? 'null' }}, '- ', '', 'list item')" class="px-2 py-1 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded transition" title="Bullet List">•</button>
                                                    <button type="button" onclick="insertMarkdown({{ $invoice['invoiceId'] ?? 'null' }}, '`', '`', 'code')" class="px-2 py-1 text-xs font-mono text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded transition" title="Code">&lt;/&gt;</button>
                                                </div>
                                                <textarea
                                                    id="comment-input-{{ $invoice['invoiceId'] ?? '' }}"
                                                    class="w-full bg-transparent border-0 focus:ring-0 text-sm text-gray-800 dark:text-gray-900 placeholder-gray-600 dark:placeholder-gray-700 resize-none font-mono"
                                                    placeholder="{{ __('dashboard.add_note') }}"
                                                    maxlength="1000"
                                                    rows="5"
                                                    onkeyup="updateCharCount({{ $invoice['invoiceId'] ?? 'null' }})"
                                                ></textarea>
                                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-yellow-400 dark:border-yellow-700">
                                                    <span id="char-count-{{ $invoice['invoiceId'] ?? '' }}" class="text-xs text-gray-600 dark:text-gray-800">
                                                        0/1000
                                                    </span>
                                                    <div class="flex gap-2">
                                                        <button
                                                            onclick="cancelAddComment({{ $invoice['invoiceId'] ?? 'null' }})"
                                                            class="px-2 py-1 text-xs text-gray-600 hover:text-gray-800 transition">
                                                            {{ __('dashboard.cancel') }}
                                                        </button>
                                                        <button
                                                            onclick="saveComment({{ $invoice['invoiceId'] ?? 'null' }})"
                                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition">
                                                            {{ __('dashboard.save_note') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
