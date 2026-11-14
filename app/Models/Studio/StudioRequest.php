<?php

namespace App\Models\Studio;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudioRequest extends Model
{
    protected $table = 'studio_requests';

    protected $fillable = [
        'uuid',
        'user_id',
        'scenario',
        'format',
        'characters',
        'environments',
        'initial_revision_video_url',
        'initial_revision_description',
        'video_url',
        'video_updated_at',
        'status',
    ];

    protected $casts = [
        'characters' => 'array',
        'environments' => 'array',
        'video_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(StudioRevision::class, 'studio_request_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
