<?php

namespace Consilience\Laravel\MessageFlow\Models;

/**
 * Eloquent model for a message that has arrived from a remote app.
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MessageFlowIn extends Model
{
    use HasFactory;

    protected $table = 'message_flow_in';

    /**
     * The default supported states for each message in the cache.
     * Other arbitrary custom states can be used too, with the application
     * giving meaning to them.
     */
    public const STATUS_NEW = 'new';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAILED = 'failed';

    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    /**
     * Just guard the auto-generated properties.
     * Note: the UUID is sent to us, since it takes on the UUID of
     * the original source message, so it's not guarded.
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

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

    public function setJsonPayloadAttribute(string $value): void
    {
        $this->attributes['payload'] = $value;
    }

    public function isNew(): bool
    {
        return $this->status === static::STATUS_NEW;
    }

    public function setComplete(): self
    {
        $this->status = static::STATUS_COMPLETE;

        return $this;
    }

    public function setFailed(): self
    {
        $this->status = static::STATUS_FAILED;

        return $this;
    }

    /**
     * Convenience method to get a value from the payload, based
     * on its "dot" path.
     */
    public function getPayloadPath(string $path, mixed $default = null): mixed
    {
        return Arr::get($this->payload, $path, $default);
    }
}
