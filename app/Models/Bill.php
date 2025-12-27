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

    protected static function booted()
    {
        static::created(function ($bill) {
            try {
                $tenant = $bill->tenant;
                $actor = auth()->id() ?? null;
                $details = "Bill recorded for {$tenant->tenant_name} - Water: {$bill->water}, Electricity: {$bill->electricity}, Internet: {$bill->internet}, Trash: {$bill->trash}, Month: {$bill->bill_month}";
                \App\Helpers\ActivityLogger::log('record_bill', $actor, $details);
            } catch (\Throwable $e) {
                // ignore logging errors
            }
        });
    }
}
