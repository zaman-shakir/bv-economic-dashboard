<?php

return [
    // Page titles
    'invoice_overview' => 'Faktura Overblik',
    'user_management' => 'Brugerstyring',
    'create_new_user' => 'Opret Ny Bruger',

    // Navigation
    'dashboard' => 'Dashboard',
    'users' => 'Brugere',
    'profile' => 'Profil',
    'log_out' => 'Log Ud',

    // Filters
    'filter_all' => 'Alle Fakturaer',
    'filter_overdue' => 'Kun Forfaldne',
    'filter_unpaid' => 'Kun Ubetalte',
    'all_employees' => 'Alle Medarbejdere',

    // Summary cards
    'total_invoices' => 'Total Fakturaer',
    'overdue_invoices' => 'Forfaldne Fakturaer',
    'unpaid_invoices' => 'Ubetalte Fakturaer',
    'total_outstanding' => 'Total Udestående',
    'employees_with_overdue' => 'Medarbejdere med Forfaldne',
    'employees_count' => 'Medarbejdere',
    'refresh_data' => 'Opdater Data',
    'loading_data' => 'Henter data...',
    'last_updated' => 'Sidst opdateret',

    // Table headers
    'invoice_number' => 'Faktura',
    'date' => 'Dato',
    'customer_number' => 'Kundenr.',
    'customer_name' => 'Kundenavn',
    'subject' => 'Overskrift',
    'amount' => 'Beløb',
    'outstanding' => 'Udestående',
    'currency' => 'Valuta',
    'status' => 'Status',
    'external_id' => 'Eksternt ID',
    'actions' => 'Handlinger',

    // Status badges
    'status_paid' => 'Betalt',
    'day_overdue' => 'dag forfalden',
    'days_overdue' => 'dage forfaldne',
    'day_remaining' => 'dag tilbage',
    'days_remaining' => 'dage tilbage',

    // Employee section
    'invoice' => 'faktura',
    'invoices' => 'fakturaer',
    'overdue_invoice' => 'forfalden faktura',
    'overdue_invoices_count' => 'forfaldne fakturaer',
    'day' => 'dag',
    'days' => 'dage',

    // Empty state
    'no_overdue_invoices' => 'Ingen forfaldne fakturaer!',
    'all_invoices_paid' => 'Alle fakturaer er betalt til tiden.',
    'no_invoices' => 'Ingen fakturaer fundet',
    'no_invoices_message' => 'Der er ingen fakturaer, der matcher dit filter.',

    // User management
    'add_new_user' => '+ Tilføj Ny Bruger',
    'name' => 'Navn',
    'email' => 'Email',
    'admin' => 'Admin',
    'user' => 'Bruger',
    'created' => 'Oprettet',
    'actions' => 'Handlinger',
    'delete' => 'Slet',
    'you' => 'Dig',

    // Create user form
    'password' => 'Adgangskode',
    'confirm_password' => 'Bekræft Adgangskode',
    'grant_admin_privileges' => 'Giv Admin Rettigheder',
    'admin_privileges_help' => 'Admin brugere kan oprette og administrere andre brugere.',
    'cancel' => 'Annuller',
    'create_user' => 'Opret Bruger',

    // Messages
    'user_created' => 'Bruger oprettet med succes.',
    'user_deleted' => 'Bruger slettet med succes.',
    'cannot_delete_self' => 'Du kan ikke slette din egen konto.',
    'confirm_delete_user' => 'Er du sikker på, at du vil slette denne bruger?',

    // Reminder messages
    'send_reminder' => 'Send Påmindelse',
    'reminder_sent_successfully' => 'Påmindelses email sendt!',
    'reminder_send_failed' => 'Kunne ikke sende påmindelse',
    'reminder_sent_recently' => 'En påmindelse blev allerede sendt for :days dage siden. Vent venligst før du sender en anden.',
    'customer_email_not_found' => 'Kunde email adresse ikke fundet.',
    'invoice_not_found' => 'Faktura ikke fundet.',
    'confirm_send_reminder' => 'Send påmindelses email til kunde?',
    'sending_reminder' => 'Sender...',

    // Employee reminder messages
    'send_employee_reminder' => 'Underret Medarbejder',
    'employee_reminder_sent' => 'Medarbejder notifikation sendt!',
    'employee_email_not_found' => 'Medarbejder email adresse ikke fundet.',
    'no_overdue_for_employee' => 'Ingen forfaldne fakturaer fundet for denne medarbejder.',
    'confirm_send_employee_reminder' => 'Send påmindelses email til medarbejder om deres forfaldne fakturaer?',

    // Sidebar Stats
    'quick_stats' => 'Hurtige Statistikker',
    'critical_invoices' => 'Kritiske Fakturaer',
    'over_30_days' => 'Over 30 Dage',
    'critical_amount' => 'Kritisk Beløb',
    'top_employees' => 'Top Medarbejdere efter Udestående',
    'critical' => 'Kritisk',

    // Search and Sort
    'search_invoices' => 'Søg fakturaer efter kunde eller nummer...',
    'sort_by' => 'Sorter efter',
    'sort_days_desc' => 'Mest forfaldne først',
    'sort_days_asc' => 'Mindst forfaldne først',
    'sort_amount_desc' => 'Højeste beløb først',
    'sort_amount_asc' => 'Laveste beløb først',
    'sort_customer' => 'Kundenavn (A-Å)',

    // Bulk Actions
    'bulk_actions' => 'Massehandlinger',
    'selected' => 'valgt',
    'send_bulk_reminders' => 'Send Massepåmindelser',
    'select_all' => 'Vælg Alle',
    'deselect_all' => 'Fravælg Alle',
    'bulk_reminder_sent' => 'Massepåmindelser sendt med succes!',
    'no_invoices_selected' => 'Vælg venligst mindst én faktura.',
];
