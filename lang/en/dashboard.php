<?php

return [
    // Page titles
    'invoice_overview' => 'Invoice Overview',
    'user_management' => 'User Management',
    'create_new_user' => 'Create New User',

    // Navigation
    'dashboard' => 'Dashboard',
    'users' => 'Users',
    'profile' => 'Profile',
    'log_out' => 'Log Out',

    // Filters
    'filter_all' => 'All Invoices',
    'filter_overdue' => 'Overdue Only',
    'filter_unpaid' => 'Unpaid Only',
    'all_employees' => 'All Employees',

    // Summary cards
    'total_invoices' => 'Total Invoices',
    'overdue_invoices' => 'Overdue Invoices',
    'unpaid_invoices' => 'Unpaid Invoices',
    'total_outstanding' => 'Total Outstanding',
    'employees_with_overdue' => 'Employees with Overdue',
    'employees_count' => 'Employees',
    'refresh_data' => 'Refresh Data',
    'loading_data' => 'Loading data...',
    'last_updated' => 'Last updated',

    // Table headers
    'invoice_number' => 'Invoice #',
    'date' => 'Date',
    'customer_number' => 'Customer #',
    'customer_name' => 'Customer Name',
    'subject' => 'Subject',
    'amount' => 'Amount',
    'outstanding' => 'Outstanding',
    'currency' => 'Currency',
    'status' => 'Status',
    'external_id' => 'External ID',
    'actions' => 'Actions',

    // Status badges
    'status_paid' => 'Paid',
    'day_overdue' => 'day overdue',
    'days_overdue' => 'days overdue',
    'day_remaining' => 'day left',
    'days_remaining' => 'days left',

    // Employee section
    'invoice' => 'invoice',
    'invoices' => 'invoices',
    'overdue_invoice' => 'overdue invoice',
    'overdue_invoices_count' => 'overdue invoices',
    'day' => 'day',
    'days' => 'days',

    // Empty state
    'no_overdue_invoices' => 'No overdue invoices!',
    'all_invoices_paid' => 'All invoices have been paid on time.',
    'no_invoices' => 'No invoices found',
    'no_invoices_message' => 'There are no invoices matching your filter.',

    // User management
    'add_new_user' => '+ Add New User',
    'name' => 'Name',
    'email' => 'Email',
    'admin' => 'Admin',
    'user' => 'User',
    'created' => 'Created',
    'actions' => 'Actions',
    'delete' => 'Delete',
    'you' => 'You',

    // Create user form
    'password' => 'Password',
    'confirm_password' => 'Confirm Password',
    'grant_admin_privileges' => 'Grant Admin Privileges',
    'admin_privileges_help' => 'Admin users can create and manage other users.',
    'cancel' => 'Cancel',
    'create_user' => 'Create User',

    // Messages
    'user_created' => 'User created successfully.',
    'user_deleted' => 'User deleted successfully.',
    'cannot_delete_self' => 'You cannot delete your own account.',
    'confirm_delete_user' => 'Are you sure you want to delete this user?',

    // Reminder messages
    'send_reminder' => 'Send Reminder',
    'reminder_sent_successfully' => 'Reminder email sent successfully!',
    'reminder_send_failed' => 'Failed to send reminder',
    'reminder_sent_recently' => 'A reminder was already sent :days days ago. Please wait before sending another.',
    'customer_email_not_found' => 'Customer email address not found.',
    'invoice_not_found' => 'Invoice not found.',
    'confirm_send_reminder' => 'Send reminder email to customer?',
    'sending_reminder' => 'Sending...',

    // Employee reminder messages
    'send_employee_reminder' => 'Notify Employee',
    'employee_reminder_sent' => 'Employee notification sent successfully!',
    'employee_email_not_found' => 'Employee email address not found.',
    'no_overdue_for_employee' => 'No overdue invoices found for this employee.',
    'confirm_send_employee_reminder' => 'Send reminder email to employee about their overdue invoices?',

    // Sidebar Stats
    'quick_stats' => 'Quick Stats',
    'critical_invoices' => 'Critical Invoices',
    'over_30_days' => 'Over 30 Days',
    'critical_amount' => 'Critical Amount',
    'top_employees' => 'Top Employees by Outstanding',
    'critical' => 'Critical',

    // Search and Sort
    'search_invoices' => 'Search invoices by customer or number...',
    'sort_by' => 'Sort by',
    'sort_days_desc' => 'Most overdue first',
    'sort_days_asc' => 'Least overdue first',
    'sort_amount_desc' => 'Highest amount first',
    'sort_amount_asc' => 'Lowest amount first',
    'sort_customer' => 'Customer name (A-Z)',

    // Bulk Actions
    'bulk_actions' => 'Bulk Actions',
    'selected' => 'selected',
    'send_bulk_reminders' => 'Send Bulk Reminders',
    'select_all' => 'Select All',
    'deselect_all' => 'Deselect All',
    'bulk_reminder_sent' => 'Bulk reminders sent successfully!',
    'no_invoices_selected' => 'Please select at least one invoice.',
];
