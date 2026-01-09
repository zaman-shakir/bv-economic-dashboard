<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Name
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                autofocus
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Password (leave blank to keep current)
                            </label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Confirm Password
                            </label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                            >
                        </div>

                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Role
                            </label>
                            <select
                                id="role"
                                name="role"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                            >
                                <option value="viewer" {{ old('role', $user->role) === 'viewer' ? 'selected' : '' }}>Viewer (Read-only)</option>
                                <option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>Employee (Limited Access)</option>
                                <option value="external_ref" {{ old('role', $user->role) === 'external_ref' ? 'selected' : '' }}>External Ref User</option>
                                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager (Full Access)</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin (Full Control)</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Employee Access -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Allowed Employees
                                </label>
                                @if($employees->count() > 0)
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            id="selectAllEmployees"
                                            class="rounded border-gray-300 dark:border-gray-700 text-green-600 shadow-sm focus:ring-green-500"
                                        >
                                        <span class="ml-2 text-xs font-medium text-green-700 dark:text-green-400">
                                            Select All (Full Access)
                                        </span>
                                    </label>
                                @endif
                            </div>
                            <div class="max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-3 bg-gray-50 dark:bg-gray-900">
                                @if($employees->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($employees as $employee)
                                            <label class="flex items-center hover:bg-gray-100 dark:hover:bg-gray-800 p-2 rounded cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    name="allowed_employees[]"
                                                    value="{{ $employee['number'] }}"
                                                    {{ in_array($employee['number'], old('allowed_employees', $user->allowed_employees ?? [])) ? 'checked' : '' }}
                                                    class="employee-checkbox rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    <span class="font-medium">{{ $employee['name'] }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400">(#{{ $employee['number'] }})</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No employees found. Sync invoices first.</p>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Check "Select All" for full access (admin/manager) or select specific employees
                            </p>
                            @error('allowed_employees')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- External Ref Access -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Allowed External References
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        id="selectAllRefs"
                                        class="rounded border-gray-300 dark:border-gray-700 text-green-600 shadow-sm focus:ring-green-500"
                                    >
                                    <span class="ml-2 text-xs font-medium text-green-700 dark:text-green-400">
                                        Select All (Full Access)
                                    </span>
                                </label>
                            </div>
                            <div class="max-h-64 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-3 bg-gray-50 dark:bg-gray-900">
                                @foreach($refGroups as $groupName => $refs)
                                    @if($refs->count() > 0)
                                        <div class="mb-3">
                                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $groupName }}</h4>
                                            <div class="space-y-1 pl-2">
                                                @foreach($refs as $ref)
                                                    <label class="flex items-center hover:bg-gray-100 dark:hover:bg-gray-800 p-1.5 rounded cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            name="allowed_refs[]"
                                                            value="{{ $ref }}"
                                                            {{ in_array($ref, old('allowed_refs', $user->allowed_external_refs ?? [])) ? 'checked' : '' }}
                                                            class="ref-checkbox rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500"
                                                        >
                                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 font-mono">
                                                            {{ $ref }}
                                                            @if(str_contains($ref, '*'))
                                                                <span class="text-xs text-blue-600 dark:text-blue-400">(wildcard)</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Check "Select All" for full access or select specific refs. Wildcards like BV-WO-* match all invoices starting with that pattern.
                            </p>
                            @error('allowed_refs')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4 space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Permissions
                            </label>

                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="can_add_comments"
                                    value="1"
                                    {{ old('can_add_comments', $user->can_add_comments) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can add comments</span>
                            </label>

                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="can_send_reminders"
                                    value="1"
                                    {{ old('can_send_reminders', $user->can_send_reminders) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can send reminders</span>
                            </label>

                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="can_sync"
                                    value="1"
                                    {{ old('can_sync', $user->can_sync) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can sync invoices</span>
                            </label>
                        </div>

                        <!-- Is Admin Checkbox (kept for backward compatibility) -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="is_admin"
                                    value="1"
                                    {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Grant Admin Privileges (Legacy)
                                </span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Use "Role: Admin" instead. This field is kept for backward compatibility.
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Select All JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All Employees
            const selectAllEmployees = document.getElementById('selectAllEmployees');
            const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');

            if (selectAllEmployees && employeeCheckboxes.length > 0) {
                selectAllEmployees.addEventListener('change', function() {
                    employeeCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });

                // Update "Select All" checkbox if individual checkboxes change
                employeeCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
                        const anyChecked = Array.from(employeeCheckboxes).some(cb => cb.checked);
                        selectAllEmployees.checked = allChecked;
                        selectAllEmployees.indeterminate = anyChecked && !allChecked;
                    });
                });

                // Initialize state
                const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(employeeCheckboxes).some(cb => cb.checked);
                selectAllEmployees.checked = allChecked;
                selectAllEmployees.indeterminate = anyChecked && !allChecked;
            }

            // Select All External Refs
            const selectAllRefs = document.getElementById('selectAllRefs');
            const refCheckboxes = document.querySelectorAll('.ref-checkbox');

            if (selectAllRefs && refCheckboxes.length > 0) {
                selectAllRefs.addEventListener('change', function() {
                    refCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });

                // Update "Select All" checkbox if individual checkboxes change
                refCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(refCheckboxes).every(cb => cb.checked);
                        const anyChecked = Array.from(refCheckboxes).some(cb => cb.checked);
                        selectAllRefs.checked = allChecked;
                        selectAllRefs.indeterminate = anyChecked && !allChecked;
                    });
                });

                // Initialize state
                const allChecked = Array.from(refCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(refCheckboxes).some(cb => cb.checked);
                selectAllRefs.checked = allChecked;
                selectAllRefs.indeterminate = anyChecked && !allChecked;
            }
        });
    </script>
</x-app-layout>
