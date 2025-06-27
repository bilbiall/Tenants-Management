<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class House extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'house_name',
        'number_of_rooms',
        'rent_amount',
        'location_id',
        'num_of_bedrooms',
        'house_status',
    ];

    //relationship with the location model
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    //relationship with the tenant model
    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }
}
