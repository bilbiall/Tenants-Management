<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\ActivityLogger;


class House extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'house_name',
        //'number_of_rooms',
        'rent_amount',
        'location_id',
        'house_type',
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

    protected static function booted()
    {
        static::created(function ($house) {
            try {
                $actor = auth()->id() ?? null;
                $details = "House created: {$house->house_name} (Rent: {$house->rent_amount})";
                ActivityLogger::log('create_house', $actor, $details);
            } catch (\Throwable $e) {
                // ignore logging errors
            }
        });
    }
}
