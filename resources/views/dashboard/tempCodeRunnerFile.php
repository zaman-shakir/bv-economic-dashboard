<?php
<div class="mb-6 flex flex-wrap items-center gap-4 lg:gap-6 w-full">
                <!-- Group 1: Filter Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashboard', ['filter' => 'all']) }}"
                       class="px-4 py-2 rounded-lg font-medium transition text-center whitespace-nowrap flex items-center gap-2 {{ $currentFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('dashboard.filter_all') }}
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'overdue']) }}"
                       class="px-4 py-2 rounded-lg font-medium transition text-center whitespace-nowrap flex items-center gap-2 {{ $currentFilter === 'overdue' ? 'bg-red-600 text-white' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('dashboard.filter_overdue') }}
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'unpaid']) }}"
                       class="px-4 py-2 rounded-lg font-medium transition text-center whitespace-nowrap flex items-center gap-2 {{ $currentFilter === 'unpaid' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('dashboard.filter_unpaid') }}
                    </a>
                </div>

                <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

                <!-- Group 2: Grouping Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('dashboard', ['filter' => $currentFilter, 'grouping' => 'employee']) }}"
                       class="px-4 py-2 rounded-lg font-medium transition text-center whitespace-nowrap flex items-center gap-2 {{ ($currentGrouping ?? 'employee') === 'employee' ? 'bg-indigo-600 text-white' : 'bg-indigo-200 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-300 dark:hover:bg-indigo-900/70' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        By Employee
                    </a>
                    <a href="{{ route('dashboard', ['filter' => $currentFilter, 'grouping' => 'other_ref']) }}"
                       class="px-4 py-2 rounded-lg font-medium transition text-center whitespace-nowrap flex items-center gap-2 {{ ($currentGrouping ?? 'employee') === 'other_ref' ? 'bg-indigo-600 text-white' : 'bg-indigo-200 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-300 dark:hover:bg-indigo-900/70' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        By Other Ref
                    </a>
                </div>

                <div class="hidden lg:flex flex-grow"></div>

                <!-- Group 3: Actions -->
                <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                    <select id="employeeFilter" onchange="filterByEmployee(this.value)"
                            class="flex-1 lg:w-20 min-w-[100px] lg:max-w-[300px] px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                        <option value="">👥 {{ __('dashboard.all_employees') }}</option>
                        @foreach($invoicesByEmployee as $emp)
                            <option value="{{ $emp['employeeNumber'] }}">
                                {{ $emp['employeeName'] }} ({{ $emp['invoiceCount'] }})
                            </option>
                        @endforeach
                    </select>

                    <a href="{{ route('dashboard', request()->all()) }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition hover:bg-gray-300 dark:hover:bg-gray-600 text-center whitespace-nowrap flex items-center gap-2"
                       title="{{ __('dashboard.refresh_data') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('dashboard.refresh_data') }}
                    </a>

                    @if($usingDatabase ?? false)
                    <button
                        id="syncButton"
                        onclick="syncNow()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap flex items-center gap-2"
                    >
                        <svg id="syncIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="syncButtonText">Sync now</span>
                    </button>
                    @endif
                </div>
            </div>