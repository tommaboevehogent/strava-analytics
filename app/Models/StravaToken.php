<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StravaToken extends Model
{
    protected $fillable = [
        'athlete_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scope',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'scope' => 'array',
        ];
    }

    public function isExpired(): bool
    {
        // Refresh a bit early to avoid racing an in-flight request.
        return $this->expires_at->subMinute()->isPast();
    }
}
