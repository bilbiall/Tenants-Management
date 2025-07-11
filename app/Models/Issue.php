<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Issue extends Model
{
     // Allow these attributes to be mass-assigned
    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'status',
    ];

     /**
     * Get the tenant who reported this issue.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
