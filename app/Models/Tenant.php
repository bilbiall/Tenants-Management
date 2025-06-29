<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//use helper for sending sms
use App\Helpers\SmsHelper;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    //all tenant db related stuff here
    protected $fillable = [
        'house_id',
        'tenant_name',
        'email',
        'phone_number',
        'date_admitted',
    ];

    //relationship for with the house model
    public function house()
    {
        return $this->belongsTo(House::class);
    }

    //relationship with bill model
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    //rship with invoices
    use HasFactory;

    // A tenant can have many invoices
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    //relationship with payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }



    //to update status of house to occupied
    protected static function booted()
    {
        static::created(function ($tenant) {
            $tenant->house->update(['house_status' => 'Occupied']);
            // 2. Prepare SMS details
            $name = $tenant->tenant_name;
            $phone = $tenant->phone_number;
            $houseName = $tenant->house->house_name;
            $rent = $tenant->house->rent_amount;
            $appName = config('app.name');

            $message = "Hello $name, welcome to $appName. You were admitted to $houseName with a monthly rent of KES $rent";

            // 3. Send SMS using your helper
            SmsHelper::sendSms($phone, $message);
        });

        static::updated(function ($tenant) {
            // Check if the house was changed
            if ($tenant->isDirty('house_id')) {
                $originalHouseId = $tenant->getOriginal('house_id');

                // Set old house to Vacant
                \App\Models\House::where('id', $originalHouseId)->update([
                    'house_status' => 'Vacant',
                ]);

                // Set new house to Occupied
                $tenant->house->update(['house_status' => 'Occupied']);
            }
        });

        static::deleted(function ($tenant) {
            $tenant->house->update(['house_status' => 'Vacant']);
        });
    }
}
