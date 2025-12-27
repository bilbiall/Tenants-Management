@extends('layouts.app')

@section('content')
<div style="max-width:720px;margin:48px auto;padding:24px;border:1px solid #eee;border-radius:8px;">
    <h2>Pesapal Payment Initiation (Simulation)</h2>
    <p><strong>Invoice:</strong> {{ $invoice->invoice_number }} — <strong>Amount:</strong> KES {{ number_format($amount, 2) }}</p>

    @if(!empty($pesapal) && !empty($pesapal['consumer_key']))
        <div style="padding:12px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:6px;margin-bottom:12px;">
            <strong>✓ Pesapal configured</strong> — Real checkout should have redirected here. If you see this, check your API keys.
        </div>
    @else
        <div style="padding:12px;background:#fff3cd;border:1px solid #ffeeba;border-radius:6px;margin-bottom:12px;">
            <strong>Notice:</strong> Pesapal credentials are not configured. Use the simulation below for local testing.
        </div>
    @endif

    <h3>Callback & IPN URLs (for Pesapal Dashboard)</h3>
    <p>When deploying to production, configure these URLs in your Pesapal merchant dashboard:</p>
    <ul>
        <li><strong>Callback URL:</strong> <code style="background:#f3f4f6;padding:8px;display:block;margin:8px 0;word-break:break-all;">{{ config('app.url') }}/api/payments/pesapal/callback</code></li>
        <li><strong>IPN Listener URL:</strong> <code style="background:#f3f4f6;padding:8px;display:block;margin:8px 0;word-break:break-all;">{{ config('app.url') }}/api/payments/pesapal/ipn</code></li>
    </ul>
    <p style="font-size:90%;color:#6b7280;">
        <strong>Note:</strong> On localhost, Pesapal cannot reach <code>http://localhost:8000</code>. 
        Use ngrok or deploy to a public server to test real payments.
    </p>

    <h3>Local Testing (Simulation)</h3>
    <p>Click the button below to simulate a successful payment without Pesapal:</p>

    @if(!empty($pending))
        <a href="{{ route('tenant.payments.pesapal.callback') }}?pending_id={{ $pending->id }}" style="display:inline-block;padding:10px 16px;background:#10b981;color:#fff;border-radius:6px;text-decoration:none;">Simulate Successful Payment</a>
    @else
        <a href="{{ route('tenant.payments.pesapal.callback') }}" style="display:inline-block;padding:10px 16px;background:#10b981;color:#fff;border-radius:6px;text-decoration:none;">Simulate Successful Payment</a>
    @endif

    <p style="margin-top:14px;color:#6b7280;font-size:90%;">After simulation, a Payment record is created and invoice totals update automatically.</p>
</div>
@endsection

