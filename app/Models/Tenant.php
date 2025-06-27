<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//use helper for sending sms
use App\Helpers\SmsHelper;

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

            $message = "Hello $name, welcome to Dakota Apartments. You were admitted to $houseName with a monthly rent of KES $rent";

            // 3. Send SMS using your helper
            SmsHelper::sendSms($phone, $message);
        });

        static::deleted(function ($tenant) {
            $tenant->house->update(['house_status' => 'Vacant']);
        });
    }
}
