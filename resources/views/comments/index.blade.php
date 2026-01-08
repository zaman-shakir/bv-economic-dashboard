<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('dashboard.all_comments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="flex gap-4">
                <!-- Total Comments -->
                <a href="{{ route('comments.page') }}"
                   class="flex-1 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.total_comments') }}</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $comments->total() }}</p>
                </a>

                <!-- Invoices with Comments (Clickable) -->
                <a href="{{ route('dashboard', ['has_comments' => '1', 'filter' => 'overdue']) }}"
                   class="flex-1 bg-blue-50 dark:bg-blue-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block">
                    <p class="text-sm text-blue-600 dark:text-blue-400">{{ __('dashboard.invoices_with_comments') }}</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ \App\Models\InvoiceComment::distinct('invoice_id')->count('invoice_id') }}</p>
                </a>

                <!-- Comments Today (Clickable) -->
                <a href="{{ route('dashboard', ['comment_date_filter' => 'today', 'filter' => 'overdue', 'has_comments' => '1']) }}"
                   class="flex-1 bg-green-50 dark:bg-green-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ __('dashboard.comments_today') }}</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ \App\Models\InvoiceComment::where('created_at', '>=', now()->startOfDay())->distinct('invoice_id')->count('invoice_id') }}
                    </p>
                </a>

                <!-- Comments This Week (Clickable) -->
                <a href="{{ route('dashboard', ['comment_date_filter' => 'week', 'filter' => 'overdue', 'has_comments' => '1']) }}"
                   class="flex-1 bg-purple-50 dark:bg-purple-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition block">
                    <p class="text-sm text-purple-600 dark:text-purple-400">{{ __('dashboard.comments_this_week') }}</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ \App\Models\InvoiceComment::where('created_at', '>=', now()->subWeek())->distinct('invoice_id')->count('invoice_id') }}
                    </p>
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
                    <div class="flex flex-col">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 invisible">
                            Actions
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition whitespace-nowrap">
                                {{ __('dashboard.search') }}
                            </button>
                            <a href="{{ route('comments.page') }}" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium rounded-lg transition whitespace-nowrap inline-block">
                                {{ __('dashboard.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Comments Grid -->
            <div class="bg-gray-50 dark:bg-gray-900/50 overflow-hidden sm:rounded-lg p-6 border border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    <span class="text-xl">📌</span>
                    {{ __('dashboard.all_comments') }}
                    <span class="text-sm font-normal text-gray-600 dark:text-gray-400">({{ $comments->total() }})</span>
                </h3>

                @if($comments->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                        @foreach($comments as $comment)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 dark:border-gray-700 flex flex-col h-full group">
                                <!-- Card Header -->
                                <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex justify-between items-start">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                            {{ substr($comment->user->name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $comment->user->name ?? __('dashboard.unknown_user') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $comment->created_at->format('M d, H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    @if($comment->invoice)
                                        <a href="{{ route('dashboard', ['search' => $comment->invoice->invoice_number]) }}"
                                           class="text-xs font-medium text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                           title="{{ __('dashboard.go_to_invoice') }}">
                                            #{{ $comment->invoice->invoice_number }}
                                        </a>
                                    @endif
                                </div>

                                <!-- Comment Body -->
                                <div class="px-5 py-4 flex-grow">
                                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                        {{ $comment->comment }}
                                    </div>
                                </div>

                                <!-- Card Footer -->
                                @if($comment->invoice)
                                    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-b-xl border-t border-gray-100 dark:border-gray-700 mt-auto">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-medium text-gray-700 dark:text-gray-300 truncate max-w-[60%]">
                                                {{ $comment->invoice->customer_name }}
                                            </span>
                                            <span class="{{ $comment->invoice->remainder > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} font-medium">
                                                {{ number_format($comment->invoice->remainder, 0, ',', '.') }} DKK
                                            </span>
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
    </style>
</x-app-layout>
