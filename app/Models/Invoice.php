<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Helpers\SmsHelper; // if your function is inside a helper class
use Illuminate\Support\Facades\Config;



class Invoice extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'status',
        'comment',
    ];

    // An invoice belongs to a tenant relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    //to send sms
    protected static function booted()
    {
        //autopopulate inv no
        static::creating(function ($invoice) {
            // Only generate if not manually set
            if (!$invoice->invoice_number) {
                $lastId = self::max('id') + 1; // or use a UUID if needed
                $invoice->invoice_number = 'INV-' . $lastId;
            }
        });

        //push amount to the database
        static::creating(function ($invoice) {
            $tenant = $invoice->tenant;
            $rent = $tenant->house->rent_amount ?? 0;
            $bills = $tenant->bills()
                ->whereMonth('bill_month', now()->month)
                ->whereYear('bill_month', now()->year)
                ->get();

            $billTotal = $bills->sum(function ($bill) {
                return $bill->water + $bill->trash + $bill->internet;
            });

            $invoice->amount = $rent + $billTotal; // <<== Important
        });

        static::created(function ($invoice) {
            $tenant = $invoice->tenant;
            $house = $tenant->house;

            $invoiceNumber = $invoice->invoice_number;
            $houseName = $house->house_name;
            $totalAmount = number_format($invoice->total_amount);
            $dueDate = \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y');
            $appName = Config::get('app.name');

            // Compose message
            $message = "Invoice $invoiceNumber: Your rent for $houseName is KES $totalAmount. Due by $dueDate. Please pay promptly. – $appName";

            // Send SMS (using helper)
            //sendSms($tenant->phone_number, $message); // If global helper
            SmsHelper::sendSms($tenant->phone_number, $message); // If inside SmsHelper class
        });
    }
}
