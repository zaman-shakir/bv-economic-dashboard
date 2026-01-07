<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            💬 {{ __('dashboard.all_comments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Comments -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.total_comments') }}</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $comments->total() }}</p>
                </div>

                <!-- Invoices with Comments (Clickable) -->
                <a href="{{ route('dashboard', ['has_comments' => '1', 'filter' => 'all']) }}"
                   class="bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 overflow-hidden shadow-sm sm:rounded-lg p-6 transition cursor-pointer group">
                    <p class="text-sm text-blue-600 dark:text-blue-400 group-hover:underline">{{ __('dashboard.invoices_with_comments') }}</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ \App\Models\InvoiceComment::distinct('invoice_id')->count('invoice_id') }}</p>
                    <p class="text-xs text-blue-500 dark:text-blue-300 mt-2">{{ __('dashboard.click_to_view') }} →</p>
                </a>

                <!-- Comments Today (Clickable) -->
                <a href="{{ route('dashboard', ['comment_date_filter' => 'today', 'filter' => 'all', 'has_comments' => '1']) }}"
                   class="bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 overflow-hidden shadow-sm sm:rounded-lg p-6 transition cursor-pointer group">
                    <p class="text-sm text-green-600 dark:text-green-400 group-hover:underline">{{ __('dashboard.comments_today') }}</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ \App\Models\InvoiceComment::where('created_at', '>=', now()->startOfDay())->distinct('invoice_id')->count('invoice_id') }}
                    </p>
                    <p class="text-xs text-green-500 dark:text-green-300 mt-2">{{ __('dashboard.click_to_view') }} →</p>
                </a>

                <!-- Comments This Week (Clickable) -->
                <a href="{{ route('dashboard', ['comment_date_filter' => 'week', 'filter' => 'all', 'has_comments' => '1']) }}"
                   class="bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 overflow-hidden shadow-sm sm:rounded-lg p-6 transition cursor-pointer group">
                    <p class="text-sm text-purple-600 dark:text-purple-400 group-hover:underline">{{ __('dashboard.comments_this_week') }}</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ \App\Models\InvoiceComment::where('created_at', '>=', now()->subWeek())->distinct('invoice_id')->count('invoice_id') }}
                    </p>
                    <p class="text-xs text-purple-500 dark:text-purple-300 mt-2">{{ __('dashboard.click_to_view') }} →</p>
                </a>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('comments.page') }}" class="flex flex-wrap gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-[300px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('dashboard.search_comments') }}
                        </label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            placeholder="{{ __('dashboard.search_comments_placeholder') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    </div>

                    <!-- User Filter -->
                    <div class="w-64">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('dashboard.filter_by_user') }}
                        </label>
                        <select
                            name="user_id"
                            id="user_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                            <option value="">{{ __('dashboard.all_users') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            {{ __('dashboard.search') }}
                        </button>
                        <a href="{{ route('comments.page') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition">
                            {{ __('dashboard.clear') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Comments Grid with Sticky Notes -->
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-gray-800 dark:to-gray-850 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    <span class="text-xl">📌</span>
                    {{ __('dashboard.all_comments') }}
                    <span class="text-sm font-normal text-gray-600 dark:text-gray-400">({{ $comments->total() }})</span>
                </h3>

                @if($comments->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                        @php
                            $stickyColors = [
                                'bg-yellow-200 dark:bg-yellow-600',
                                'bg-pink-200 dark:bg-pink-600',
                                'bg-blue-200 dark:bg-blue-600',
                                'bg-green-200 dark:bg-green-600',
                                'bg-purple-200 dark:bg-purple-600'
                            ];
                        @endphp

                        @foreach($comments as $index => $comment)
                            @php
                                $colorClass = $stickyColors[$index % count($stickyColors)];
                                $rotation = ($index % 3 === 0) ? '-rotate-1' : (($index % 3 === 1) ? 'rotate-1' : '');
                            @endphp

                            <div class="sticky-note {{ $colorClass }} {{ $rotation }} p-4 rounded shadow-md transform hover:scale-105 transition-transform duration-200">
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-2 pb-2 border-b border-current/20">
                                    <div>
                                        <div class="text-xs font-bold text-gray-800 dark:text-gray-900">
                                            {{ $comment->user->name ?? __('dashboard.unknown_user') }}
                                        </div>
                                        <div class="text-[10px] text-gray-600 dark:text-gray-800 opacity-75">
                                            {{ $comment->created_at->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                    @if($comment->invoice)
                                        <a href="{{ route('dashboard', ['search' => $comment->invoice->invoice_number]) }}"
                                           class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold bg-white/50 hover:bg-white/80 dark:bg-gray-900/30 dark:hover:bg-gray-900/50 rounded transition"
                                           title="{{ __('dashboard.go_to_invoice') }}">
                                            <span>📄</span>
                                            #{{ $comment->invoice->invoice_number }}
                                        </a>
                                    @endif
                                </div>

                                <!-- Comment Text -->
                                <div class="text-sm text-gray-800 dark:text-gray-900 whitespace-pre-wrap leading-snug">
                                    {{ $comment->comment }}
                                </div>

                                <!-- Invoice Info -->
                                @if($comment->invoice)
                                    <div class="mt-3 pt-2 border-t border-current/20 text-[10px] text-gray-600 dark:text-gray-800 opacity-75">
                                        <div class="flex justify-between">
                                            <span>{{ $comment->invoice->customer_name }}</span>
                                            <span>{{ number_format($comment->invoice->remainder, 0, ',', '.') }} DKK</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $comments->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('dashboard.no_comments_found') }}</p>
                        <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">{{ __('dashboard.no_comments_found_message') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Sticky Note Styling */
        .sticky-note {
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }

        .sticky-note:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15), 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .sticky-note::before {
            content: '';
            position: absolute;
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 8px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 2px;
        }
    </style>
</x-app-layout>
