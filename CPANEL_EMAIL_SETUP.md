# cPanel Email Setup Guide

This guide explains how to configure the dashboard to use your cPanel mail server instead of Resend.

## Step 1: Create Email Account in cPanel

1. Log into your cPanel (https://dash.billigventilation.dk:2083 or your server's cPanel URL)
2. Go to **Email Accounts** section
3. Click **Create** button
4. Fill in the form:
   - **Email**: `dashboard@billigventilation.dk`
   - **Password**: Create a strong password (save this - you'll need it in .env)
   - **Storage Space**: 250 MB is enough for this purpose
5. Click **Create**

### Alternative Email Addresses
If `dashboard@billigventilation.dk` is already in use, you can use:
- `invoices@billigventilation.dk` - Invoice-specific
- `reminders@billigventilation.dk` - Reminder-specific
- `system@billigventilation.dk` - Generic system emails
- `notifications@billigventilation.dk` - Notification emails

Make sure to update the email address in `.env` file if you use a different one.

## Step 2: Update .env File on Server

SSH into your server or use cPanel File Manager to edit `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.billigventilation.dk
MAIL_PORT=465
MAIL_USERNAME=dashboard@billigventilation.dk
MAIL_PASSWORD=the_password_you_created_in_step1
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="dashboard@billigventilation.dk"
MAIL_FROM_NAME="${APP_NAME}"
```

**Important:** Replace `the_password_you_created_in_step1` with the actual password from Step 1.

**Note:** These settings are based on your cPanel's SMTP configuration (mail.billigventilation.dk, port 465 with SSL).

### Alternative SMTP Settings

The recommended settings above (port 465 with SSL) should work. If you experience issues, try these alternatives:

**Option 1: TLS on port 587** (if your server supports STARTTLS)
```env
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Option 2: Use localhost instead of domain**
```env
MAIL_HOST=localhost
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

**Option 3: Non-SSL (not recommended, only as last resort)**
```env
MAIL_HOST=mail.billigventilation.dk
MAIL_PORT=25
MAIL_ENCRYPTION=null
```

## Step 3: Clear Cache

After updating .env, clear Laravel cache:

```bash
cd /path/to/your/project
php artisan config:clear
php artisan cache:clear
```

Or use the browser route: `https://dash.billigventilation.dk/clear-all-cache`

## Step 4: Test Email Sending

### Method 1: Use Laravel Tinker
```bash
php artisan tinker
```

Then run:
```php
Mail::raw('Test email from BV Dashboard', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

Check your inbox (and spam folder) for the test email.

### Method 2: Use the Dashboard
1. Go to the dashboard with overdue invoices
2. Click "Send Email" button on any invoice
3. Check if the email is sent successfully

## Step 5: Configure SPF Record (Optional but Recommended)

To prevent emails from going to spam, add an SPF record in cPanel:

1. Go to **Zone Editor** in cPanel
2. Find your domain: `billigventilation.dk`
3. Add a **TXT** record:
   - **Name**: `billigventilation.dk` (or leave blank for root domain)
   - **TTL**: 14400
   - **Type**: TXT
   - **Record**: `v=spf1 a mx ip4:YOUR_SERVER_IP ~all`

Replace `YOUR_SERVER_IP` with your actual server IP address.

## Step 6: Configure DKIM (Optional but Recommended)

For even better deliverability:

1. Go to **Email Deliverability** in cPanel
2. Find `billigventilation.dk`
3. Click **Manage** next to DKIM
4. Click **Install the suggested record**

## Troubleshooting

### Problem: Emails not sending
**Solution:** Check Laravel logs at `storage/logs/laravel.log`

### Problem: Connection refused
**Solution:** Try different MAIL_HOST values:
- `localhost`
- `127.0.0.1`
- `mail.billigventilation.dk`

### Problem: Authentication failed
**Solution:**
- Verify the email password in cPanel
- Make sure you're using the full email as username: `dashboard@billigventilation.dk`

### Problem: Emails go to spam
**Solution:**
- Configure SPF and DKIM (see steps above)
- Make sure you're using a professional email address like `dashboard@billigventilation.dk`
- Avoid generic names like "noreply" which are often flagged as spam

## Verification Checklist

- [ ] Email account created in cPanel
- [ ] .env file updated with correct credentials
- [ ] Laravel cache cleared
- [ ] Test email sent successfully
- [ ] SPF record configured
- [ ] DKIM configured
- [ ] Emails arriving in inbox (not spam)

## Switching Back to Resend

If cPanel mail doesn't work well, you can switch back to Resend:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="dashboard@billigventilation.dk"
MAIL_FROM_NAME="${APP_NAME}"
RESEND_API_KEY=re_8yWgna36_EeMUbeNs56CKgT8K8JpLUKMy
```

Then clear cache again.
