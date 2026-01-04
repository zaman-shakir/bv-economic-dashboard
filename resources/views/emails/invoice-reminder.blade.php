<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $locale === 'da' ? 'Faktura Påmindelse' : 'Invoice Reminder' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="color: #dc3545; margin-top: 0;">
            @if($locale === 'da')
                Påmindelse om forfalden faktura
            @else
                Overdue Invoice Reminder
            @endif
        </h2>
    </div>

    <p>
        @if($locale === 'da')
            Kære {{ $customerName }},
        @else
            Dear {{ $customerName }},
        @endif
    </p>

    <p>
        @if($locale === 'da')
            Dette er en venlig påmindelse om, at følgende faktura er forfalden til betaling:
        @else
            This is a friendly reminder that the following invoice is overdue for payment:
        @endif
    </p>

    <div style="background-color: #fff; border: 2px solid #dc3545; border-radius: 8px; padding: 20px; margin: 20px 0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">
                    @if($locale === 'da')
                        Fakturanummer:
                    @else
                        Invoice Number:
                    @endif
                </td>
                <td style="padding: 8px 0;">{{ $invoice['invoiceNumber'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">
                    @if($locale === 'da')
                        Udestående beløb:
                    @else
                        Amount Outstanding:
                    @endif
                </td>
                <td style="padding: 8px 0; color: #dc3545; font-weight: bold; font-size: 18px;">
                    {{ number_format($invoice['remainder'], 2, ',', '.') }} DKK
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">
                    @if($locale === 'da')
                        Forfaldsdato:
                    @else
                        Due Date:
                    @endif
                </td>
                <td style="padding: 8px 0;">{{ \Carbon\Carbon::parse($invoice['dueDate'])->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">
                    @if($locale === 'da')
                        Dage forfalden:
                    @else
                        Days Overdue:
                    @endif
                </td>
                <td style="padding: 8px 0; color: #dc3545;">{{ $invoice['daysOverdue'] }} {{ $invoice['daysOverdue'] === 1 ? ($locale === 'da' ? 'dag' : 'day') : ($locale === 'da' ? 'dage' : 'days') }}</td>
            </tr>
            @if($invoice['overskrift'])
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">
                    @if($locale === 'da')
                        Beskrivelse:
                    @else
                        Description:
                    @endif
                </td>
                <td style="padding: 8px 0;">{{ $invoice['overskrift'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p>
        @if($locale === 'da')
            Venligst sørg for at betale det udestående beløb snarest muligt.
        @else
            Please ensure that the outstanding amount is paid as soon as possible.
        @endif
    </p>

    <p>
        @if($locale === 'da')
            Hvis du har spørgsmål eller allerede har betalt denne faktura, bedes du kontakte os.
        @else
            If you have any questions or have already paid this invoice, please contact us.
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
        <p style="margin: 5px 0; font-weight: bold;">BilligVentilation</p>
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
