<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
}
