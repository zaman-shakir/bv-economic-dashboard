<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            🔔 {{ __('dashboard.reminder_notification_center') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.total_reminders') }}</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ __('dashboard.successfully_sent') }}</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['sent'] }}</p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ __('dashboard.failed') }}</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed'] }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-blue-600 dark:text-blue-400">{{ __('dashboard.today') }}</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['today'] }}</p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ __('dashboard.this_week') }}</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['this_week'] }}</p>
                </div>
            </div>

            <!-- Reminders Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('dashboard.all_sent_reminders') }}</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.date_time') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.invoice_number') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.customer') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.email') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('dashboard.amount_due') }}</th>
                                <th class="px-6 py-3 text-center font-medium">{{ __('dashboard.status') }}</th>
                                <th class="px-6 py-3 text-left font-medium">{{ __('dashboard.sent_by') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($reminders as $reminder)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $reminder->created_at->format('d-m-Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        #{{ $reminder->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $reminder->customer_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $reminder->customer_email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                        {{ number_format($reminder->amount_due, 2, ',', '.') }} DKK
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($reminder->email_sent)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                                ✓ {{ __('dashboard.sent') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400"
                                                  title="{{ $reminder->email_error }}">
                                                ✗ {{ __('dashboard.failed') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $reminder->sentBy->name ?? __('dashboard.system') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('dashboard.no_reminders_sent') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($reminders->hasPages())
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                        {{ $reminders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
