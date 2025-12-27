# Pesapal Payment Integration Guide

## Overview

This Laravel application integrates with Pesapal for payment processing. The integration uses environment-based URLs that automatically work on localhost and scale to production without code changes.

## Route Structure

All Pesapal payment routes are defined once and use the same paths across environments:

### Callback & IPN Endpoints

**Callback Endpoint (Payment Status):**
```
POST /api/payments/pesapal/callback
```

**IPN Listener Endpoint (Instant Payment Notification):**
```
POST /api/payments/pesapal/ipn
```

### Environment-Based URLs

The full URLs are built dynamically from `config('app.url')`:

#### Local Development
```
.env:
APP_URL=http://localhost:8000

Callback: http://localhost:8000/api/payments/pesapal/callback
IPN:      http://localhost:8000/api/payments/pesapal/ipn
```

#### Production
```
.env:
APP_URL=https://yourdomain.com

Callback: https://yourdomain.com/api/payments/pesapal/callback
IPN:      https://yourdomain.com/api/payments/pesapal/ipn
```

**No code changes required when deploying** — only the `.env` file needs to be updated.

## Pesapal Merchant Dashboard Configuration

1. Log in to your Pesapal merchant dashboard
2. Go to **Settings > API Configuration**
3. Set the following URLs:
   - **Callback URL:** `https://yourdomain.com/api/payments/pesapal/callback`
   - **IPN Listener URL:** `https://yourdomain.com/api/payments/pesapal/ipn`

These URLs will be displayed in the payment initiation page for reference.

## Admin Settings

Configure Pesapal credentials in **Admin > Settings > Payments tab**:

- **Consumer Key:** Your Pesapal API consumer key
- **Consumer Secret:** Your Pesapal API consumer secret
- **Webhook Secret:** Your Pesapal webhook HMAC secret (for signature verification)
- **Callback URL:** (Optional) Override the default callback URL
- **Use Pesapal Sandbox:** Toggle between sandbox and live environment
- **Currency:** Default is KES

## Local Testing (Localhost)

**Important:** Pesapal cannot directly call `http://localhost:8000`.

### Option 1: Use ngrok (Recommended)
```bash
# Install ngrok: https://ngrok.com/download
ngrok http 8000

# You'll get a public URL like: https://abc123.ngrok.io
# Update .env:
APP_URL=https://abc123.ngrok.io

# Now configure Pesapal dashboard with:
# Callback: https://abc123.ngrok.io/api/payments/pesapal/callback
# IPN:      https://abc123.ngrok.io/api/payments/pesapal/ipn
```

### Option 2: Use Simulation Mode (for development)
Click "Simulate Successful Payment" on the payment initiation page to test locally without Pesapal credentials.

## Payment Flow

### 1. Tenant Initiates Payment
- Tenant clicks "Pay Now" on invoice
- Creates a `PendingPayment` record
- Redirects to Pesapal checkout (if configured) or simulation page

### 2. Pesapal Processes Payment
- Tenant completes payment on Pesapal
- Pesapal sends callback/IPN to configured URL

### 3. Application Records Payment
- Callback/IPN handler verifies HMAC signature
- Creates a `Payment` record
- Updates invoice status and tenant balance
- Sends SMS confirmation to tenant

## Signature Verification

All callbacks and IPN messages from Pesapal include an `X-Pesapal-Signature` header with an HMAC-SHA256 signature.

The application verifies this signature using the `webhook_secret` configured in the Pesapal settings:

```php
// In PesapalController
$signature = $request->header('X-Pesapal-Signature');
if (!$pesapal->verifySignature($signature, $payload)) {
    return response()->json(['message' => 'invalid signature'], 403);
}
```

## Files & Structure

```
app/
  Http/Controllers/
    PesapalController.php         # Payment callbacks and IPN handlers
  Models/
    PendingPayment.php            # Stores in-progress payments
    Payment.php                   # Completed payments (existing)
  Services/
    PesapalService.php            # API client and signature verification
  Filament/
    Resources/
      PendingPaymentResource.php  # Admin UI for tracking pending payments
    Pages/
      Settings.php                # Admin settings for Pesapal credentials

routes/
  api.php                         # Callback and IPN routes (unauthenticated)
  web.php                         # Payment initiation routes (authenticated)

resources/
  views/
    pesapal/
      initiate.blade.php          # Payment initiation page

database/
  migrations/
    2025_12_27_000001_create_pending_payments_table.php
```

## Testing Checklist

- [ ] Pesapal credentials entered in Admin Settings
- [ ] `APP_URL` set correctly in `.env`
- [ ] Callback/IPN URLs configured in Pesapal dashboard
- [ ] Test simulation on localhost (no credentials needed)
- [ ] Test with ngrok + sandbox credentials
- [ ] Verify SMS confirmation sent after payment
- [ ] Check `PendingPayment` and `Payment` records in admin UI
- [ ] Deploy to production with updated `.env` and Pesapal URLs

## Troubleshooting

### "No pending payment found"
- Check that the `PendingPayment` record was created
- Verify the reference matches between callback and pending record
- Check admin UI under "Pending Payments"

### "Invalid signature"
- Ensure `webhook_secret` in settings matches Pesapal dashboard
- Verify HTTPS is used in production (callbacks must be over HTTPS)

### Pesapal redirecting back immediately
- Check `consumer_key` and `consumer_secret` are correct
- Verify Pesapal is in sandbox mode if using sandbox credentials
- Review Pesapal API response in `PendingPayment` meta field (admin UI)

## Next Steps

1. Obtain Pesapal sandbox credentials from [developer.pesapal.com](https://developer.pesapal.com)
2. Enter credentials in Admin Settings
3. Configure callback/IPN URLs in Pesapal dashboard
4. Test with ngrok on localhost
5. Deploy to production server
6. Update `.env` with production domain
7. Update Pesapal dashboard with production URLs
8. Test live payments
