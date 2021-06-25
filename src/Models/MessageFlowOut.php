<?php

namespace Consilience\Laravel\MessageFlow\Models;

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

    /**
     * Default values on creation.
     *
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_NEW,
        'name' => self::DEFAULT_NAME,
        'payload' => self::DEFAULT_PAYLOAD,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'payload' => 'json',
    ];

    /**
     * Just guard the auto-generated properties.
     *
     * @var array
     */
    protected $guarded = [
        'uuid',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate the primary key UUID when creating a new instance.

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Indicates the primary key is not an incrementing integer.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Returns the primary key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Name the primary key appropriately.
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'uuid';
    }

    /**
     * Check if the message has been added to the queue yet.
     *
     * @return boolean
     */
    public function isSent(): bool
    {
        return $this->status === static::STATUS_QUEUED || $this->status === static::STATUS_COMPLETE;
    }

    /**
     * Check if the record is new. Used by observer to confirm ready to send.
     *
     * @return boolean
     */
    public function isNew(): bool
    {
        return $this->status === static::STATUS_NEW;
    }

    /**
     * Select only records that need to be dispatched to the queue.
     * These are NEW and also FAILED that will need to be sent again.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToSend($query)
    {
        $query->whereIn('status', [static::STATUS_NEW, static::STATUS_FAILED]);
    }

    public function scopeIsQueued($query)
    {
        $query->where('status', '=', static::STATUS_QUEUED);
    }

    /**
     * Return the payload as a JSON encoded string.
     *
     * @return string
     */
    public function getJsonPayloadAttribute(): string
    {
        return $this->attributes['payload'] ?? self::DEFAULT_PAYLOAD;
    }
}
