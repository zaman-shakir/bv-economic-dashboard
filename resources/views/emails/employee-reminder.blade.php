<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $locale === 'da' ? 'Forfaldne Fakturaer Påmindelse' : 'Overdue Invoices Reminder' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 700px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="color: #dc3545; margin-top: 0;">
            @if($locale === 'da')
                📋 Forfaldne Fakturaer Påmindelse
            @else
                📋 Overdue Invoices Reminder
            @endif
        </h2>
    </div>

    <p>
        @if($locale === 'da')
            Hej {{ $employeeName }},
        @else
            Hello {{ $employeeName }},
        @endif
    </p>

    <p>
        @if($locale === 'da')
            Dette er en påmindelse om, at du har <strong>{{ $employeeData['invoiceCount'] }} forfalden{{ $employeeData['invoiceCount'] === 1 ? '' : 'e' }} faktura{{ $employeeData['invoiceCount'] === 1 ? '' : 'er' }}</strong> der kræver din opmærksomhed.
        @else
            This is a reminder that you have <strong>{{ $employeeData['invoiceCount'] }} overdue invoice{{ $employeeData['invoiceCount'] === 1 ? '' : 's' }}</strong> that require your attention.
        @endif
    </p>

    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 0; font-weight: bold;">
            @if($locale === 'da')
                Total udestående: {{ number_format($employeeData['totalRemainder'], 2, ',', '.') }} DKK
            @else
                Total Outstanding: {{ number_format($employeeData['totalRemainder'], 2, ',', '.') }} DKK
            @endif
        </p>
    </div>

    <h3 style="color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 10px;">
        @if($locale === 'da')
            Dine Forfaldne Fakturaer
        @else
            Your Overdue Invoices
        @endif
    </h3>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #6c757d;">
                    @if($locale === 'da')
                        Kundenr.
                    @else
                        Customer #
                    @endif
                </th>
                <th style="padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #6c757d;">
                    @if($locale === 'da')
                        Kunde
                    @else
                        Customer
                    @endif
                </th>
                <th style="padding: 12px; text-align: right; font-size: 12px; text-transform: uppercase; color: #6c757d;">
                    @if($locale === 'da')
                        Udestående
                    @else
                        Outstanding
                    @endif
                </th>
                <th style="padding: 12px; text-align: center; font-size: 12px; text-transform: uppercase; color: #6c757d;">
                    @if($locale === 'da')
                        Dage Forfalden
                    @else
                        Days Overdue
                    @endif
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($employeeData['invoices'] as $invoice)
                <tr style="border-bottom: 1px solid #dee2e6; {{ $invoice['daysOverdue'] > 30 ? 'background-color: #f8d7da;' : ($invoice['daysOverdue'] > 14 ? 'background-color: #fff3cd;' : '') }}">
                    <td style="padding: 12px; font-size: 14px;">
                        {{ $invoice['kundenr'] }}
                    </td>
                    <td style="padding: 12px; font-size: 14px;">
                        <strong>{{ $invoice['kundenavn'] }}</strong>
                        @if($invoice['overskrift'])
                            <br><span style="font-size: 12px; color: #6c757d;">{{ Str::limit($invoice['overskrift'], 50) }}</span>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: right; font-size: 14px; font-weight: bold; color: #dc3545;">
                        {{ number_format($invoice['remainder'], 2, ',', '.') }} DKK
                    </td>
                    <td style="padding: 12px; text-align: center; font-size: 14px;">
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;
                            {{ $invoice['daysOverdue'] > 30 ? 'background-color: #dc3545; color: white;' : ($invoice['daysOverdue'] > 14 ? 'background-color: #ffc107; color: #000;' : 'background-color: #6c757d; color: white;') }}">
                            {{ $invoice['daysOverdue'] }} {{ $invoice['daysOverdue'] === 1 ? ($locale === 'da' ? 'dag' : 'day') : ($locale === 'da' ? 'dage' : 'days') }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="background-color: #e7f3ff; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #0d6efd;">
            @if($locale === 'da')
                💡 Hvad skal du gøre?
            @else
                💡 What should you do?
            @endif
        </h4>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li style="margin-bottom: 8px;">
                @if($locale === 'da')
                    Kontakt kunderne for at følge op på betalingsstatus
                @else
                    Contact customers to follow up on payment status
                @endif
            </li>
            <li style="margin-bottom: 8px;">
                @if($locale === 'da')
                    Send påmindelser til kunder med røde fakturaer (>30 dage)
                @else
                    Send reminders to customers with red invoices (>30 days)
                @endif
            </li>
            <li style="margin-bottom: 8px;">
                @if($locale === 'da')
                    Tjek om der er problemer der forhindrer betaling
                @else
                    Check if there are any issues preventing payment
                @endif
            </li>
        </ul>
    </div>

    <p>
        @if($locale === 'da')
            Venligst håndter disse fakturaer snarest muligt.
        @else
            Please handle these invoices as soon as possible.
        @endif
    </p>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
        <p style="margin: 0;">
            @if($locale === 'da')
                Med venlig hilsen,
            @else
                Best regards,
            @endif
        </p>
        <p style="margin: 5px 0; font-weight: bold;">BilligVentilation Dashboard</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; font-size: 12px; color: #6c757d;">
        <p style="margin: 0;">
            @if($locale === 'da')
                Dette er en automatisk påmindelse sendt fra BilligVentilation Dashboard.
            @else
                This is an automated reminder sent from BilligVentilation Dashboard.
            @endif
        </p>
    </div>
</body>
</html>
