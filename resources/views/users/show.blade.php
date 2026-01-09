<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('User Details') }}: {{ $user->name }}
            </h2>
            <a href="{{ route('users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded transition">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Basic Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Name:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $user->email }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Role:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</span>
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400">
                                        Blocked
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Created:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated:</span>
                                <p class="text-gray-900 dark:text-gray-100">{{ $user->updated_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Permissions</h3>
                        <div class="flex flex-wrap gap-2">
                            @if($user->can_add_comments)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                    ✓ Can add comments
                                </span>
                            @endif
                            @if($user->can_send_reminders)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                    ✓ Can send reminders
                                </span>
                            @endif
                            @if($user->can_sync)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                    ✓ Can sync invoices
                                </span>
                            @endif
                            @if($user->isAdmin())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400">
                                    ✓ Admin privileges
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Allowed Employees -->
                    @if($user->allowed_employees && count($user->allowed_employees) > 0)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allowed Employees ({{ count($user->allowed_employees) }})</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->allowed_employees as $employeeNumber)
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    #{{ $employeeNumber }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @elseif($user->canViewAllInvoices())
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allowed Employees</h3>
                        <p class="text-gray-600 dark:text-gray-400 italic">Full access - can view all employees</p>
                    </div>
                    @else
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allowed Employees</h3>
                        <p class="text-gray-600 dark:text-gray-400 italic">No employee access assigned</p>
                    </div>
                    @endif

                    <!-- Allowed External References -->
                    @if($user->allowed_external_refs && count($user->allowed_external_refs) > 0)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allowed External References ({{ count($user->allowed_external_refs) }})</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->allowed_external_refs as $ref)
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-mono bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $ref }}
                                    @if(str_contains($ref, '*'))
                                        <span class="ml-1 text-xs text-blue-600 dark:text-blue-400">(wildcard)</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @elseif($user->canViewAllInvoices())
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allowed External References</h3>
                        <p class="text-gray-600 dark:text-gray-400 italic">Full access - can view all external references</p>
                    </div>
                    @else
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Allowed External References</h3>
                        <p class="text-gray-600 dark:text-gray-400 italic">No external reference access assigned</p>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                        <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                            Edit User
                        </a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.toggleStatus', $user) }}" method="POST" class="inline">
                                @csrf
                                @if($user->is_active)
                                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded transition" onclick="return confirm('Are you sure you want to block this user?');">
                                        Block User
                                    </button>
                                @else
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded transition">
                                        Activate User
                                    </button>
                                @endif
                            </form>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    Delete User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
