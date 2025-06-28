<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class Bill extends Model
{
    //fillables
    protected $fillable = [
        'tenant_id',
        'water',
        'electricity',
        'internet',
        'trash',
        'bill_month',
        'note',
    ];

    //relationsgip with tenant model
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
