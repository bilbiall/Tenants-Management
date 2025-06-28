<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Helpers\SmsHelper; // if your function is inside a helper class
use Illuminate\Support\Facades\Config;


class Invoice extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'comment',
    ];

    // An invoice belongs to a tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
