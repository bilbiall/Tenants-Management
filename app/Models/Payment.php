<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


use App\Models\Tenant;
use App\Models\Invoice;

use App\Helpers\SmsHelper;


class Payment extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'invoice_id',
        'amount_paid', 'balance',
        'payment_reference', 'payment_date',
        'note',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    //model listener for updating invoice status
    protected static function booted()
{

    static::created(function ($payment) {
        $invoice = $payment->invoice;
        $tenant = $payment->tenant;

        // 🔸 Sum all payments for this invoice
        $totalPaid = $invoice->payments()->sum('amount_paid');

        // 🔸 Calculate new balance for the invoice
        $invoiceBalance = $invoice->amount - $totalPaid;

        // 🔸 Update the invoice's balance field
        $invoice->balance = $invoiceBalance;

        // 🔸 Save the current payment's balance too
        $payment->balance = $invoiceBalance;
        $payment->save(); // Important to persist it

        // 🔸 Update the invoice status based on new balance
        if ($invoiceBalance <= 0) {
            $invoice->status = 'paid';
        } elseif ($invoiceBalance < $invoice->amount) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'unpaid';
        }

        $invoice->save();

        // 🔸 Update tenant balance (overpaid = negative, underpaid = positive)
        // This value will affect the next invoice's expected amount
        $tenant->balance = $invoiceBalance;
        $tenant->save();

        // 🔸 Send SMS confirmation
        $message = "Hi {$tenant->tenant_name}, we've received your payment of KES {$payment->amount_paid} for Invoice #{$invoice->invoice_number}. Thank you. - " . config('app.name');
        \App\Helpers\SmsHelper::sendSms($tenant->phone_number, $message);
    });
    /*static::created(function ($payment) {
        $invoice = $payment->invoice;
         $tenant = $payment->tenant;

        // Calculate total paid so far
        $totalPaid = $invoice->payments()->sum('amount_paid');

        $invoice->balance = $invoice->amount - $totalPaid;
        $balance = $invoice->amount - $totalPaid;
        // ✅ Save balance to the current payment
        $payment->balance = $balance;
        $payment->save(); // This is crucial

        if ($invoice->balance <= 0) {
            $invoice->status = 'paid';
        } elseif ($invoice->balance < $invoice->amount) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'unpaid';
        }

        $invoice->save();

        // ✅ Send SMS confirmation
        $message = "Hi {$tenant->tenant_name}, we've received your payment of KES {$payment->amount_paid} for Invoice #{$invoice->invoice_number}. Thank you. - " . config('app.name');
        SmsHelper::sendSms($tenant->phone_number, $message);
    });*/
}

}
