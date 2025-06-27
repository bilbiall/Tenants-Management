<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        });

        static::deleted(function ($tenant) {
            $tenant->house->update(['house_status' => 'Vacant']);
        });
    }
}
