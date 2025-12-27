<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\ActivityLogger;


class Location extends Model
{
    // app/Models/Location.php
    use HasFactory;

    protected $fillable = [
        'location_name',
        'geo_id',
    ];

    public function houses()
    {
        return $this->hasMany(House::class);
    }

    protected static function booted()
    {
        static::created(function ($location) {
            try {
                $actor = auth()->id() ?? null;
                $details = "Location created: {$location->location_name} (geo_id: {$location->geo_id})";
                ActivityLogger::log('create_location', $actor, $details);
            } catch (\Throwable $e) {
                // ignore logging errors
            }
        });
    }
}
