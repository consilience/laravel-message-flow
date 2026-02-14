<?php

namespace Consilience\Laravel\MessageFlow\Models;

/**
 * Eloquent model for an outbound message.
 */

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MessageFlowOut extends Model
{
    use HasFactory;

    protected $table = 'message_flow_out';

    /**
     * The default supported states for each message in the cache.
     * Other arbitrary states can be used, and will be ignored by
     * this package.
     */
    public const STATUS_NEW = 'new';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAILED = 'failed';

    public const DEFAULT_NAME = 'default';

    public const DEFAULT_PAYLOAD = '{}';

    protected $attributes = [
        'status' => self::STATUS_NEW,
        'name' => self::DEFAULT_NAME,
        'payload' => self::DEFAULT_PAYLOAD,
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    protected $guarded = [
        'uuid',
        'created_at',
        'updated_at',
    ];

    protected static function booted(): void
    {
        // Auto-generate the primary key UUID when creating a new instance.

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::orderedUuid()->toString();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getKeyName(): string
    {
        return 'uuid';
    }

    public function isSent(): bool
    {
        return $this->status === static::STATUS_QUEUED || $this->status === static::STATUS_COMPLETE;
    }

    public function isNew(): bool
    {
        return $this->status === static::STATUS_NEW;
    }

    /**
     * Select only records that need to be dispatched to the queue.
     * These are NEW and also FAILED that will need to be sent again.
     */
    public function scopeToSend(Builder $query): void
    {
        $query->whereIn('status', [static::STATUS_NEW, static::STATUS_FAILED]);
    }

    public function scopeIsQueued(Builder $query): void
    {
        $query->where('status', '=', static::STATUS_QUEUED);
    }

    public function getJsonPayloadAttribute(): string
    {
        return $this->attributes['payload'] ?? self::DEFAULT_PAYLOAD;
    }
}