<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


use App\Models\Tenant;
use App\Models\Invoice;

class Payment extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'invoice_id',
        'amount_paid', 'balance',
        'payment_reference', 'note',
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

        // Calculate total paid so far
        $totalPaid = $invoice->payments()->sum('amount_paid');

        $invoice->balance = $invoice->amount - $totalPaid;

        if ($invoice->balance <= 0) {
            $invoice->status = 'paid';
        } elseif ($invoice->balance < $invoice->amount) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'unpaid';
        }

        $invoice->save();
    });
}

}
