<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    // app/Models/Location.php

    protected $fillable = [
        'location_name',
        'geo_id',
    ];

}
