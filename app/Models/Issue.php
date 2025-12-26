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

    protected static function booted()
    {
        static::updated(function ($issue) {
            if ($issue->wasChanged('status')) {
                // Notify tenant user if exists
                $tenantUser = $issue->tenant->user ?? null;
                if ($tenantUser) {
                    $tenantUser->notify(new \App\Notifications\DatabaseNotification(
                        'Issue Status Updated',
                        "Your issue '{$issue->title}' status changed to {$issue->status}",
                        null
                    ));
                }

                // Notify admins about the status change
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\DatabaseNotification(
                        'Issue Status Updated',
                        "Issue '{$issue->title}' status changed to {$issue->status}",
                        null
                    ));
                }
            }
        });
    }
}
