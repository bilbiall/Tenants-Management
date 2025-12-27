<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Helpers\SmsHelper; // if your function is inside a helper class
use App\Helpers\SmsTemplateHelper;
use Illuminate\Support\Facades\Config;
use App\Helpers\ActivityLogger;



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
        'balance',
        'comment',
    ];

    // An invoice belongs to a tenant relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    //payment invoice relationship
    public function payments()
    {
        return $this->hasMany(Payment::class);
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

            $message = SmsTemplateHelper::render('template_invoice', [
                'tenant_name' => $tenant->tenant_name,
                'invoice_number' => $invoice->invoice_number,
                'amount' => number_format($invoice->amount),
                'due_date' => \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y'),
            ]);

            SmsHelper::sendSms($tenant->phone_number, $message);
            // Send database notification to admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\DatabaseNotification(
                    'New Invoice Created',
                    "Invoice {$invoice->invoice_number} created for {$tenant->tenant_name}",
                    null
                ));
            }

            // Record activity log (who performed the action if available)
            try {
                $actor = auth()->id() ?? null;
                ActivityLogger::log('send_invoice', $actor, "Invoice {$invoice->invoice_number} created for {$tenant->tenant_name}");
            } catch (\Throwable $e) {
                // don't break invoice creation on logging failure
            }
        });
    }
}
