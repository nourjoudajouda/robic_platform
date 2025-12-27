<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'user_type',
        'action',
        'description',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'route',
        'method',
        'request_data',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'request_data' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action (if from user panel)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that performed the action (if from admin panel)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the model that was affected by the action
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by user type
     */
    public function scopeUserType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope to filter by action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by model type
     */
    public function scopeModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }
}
