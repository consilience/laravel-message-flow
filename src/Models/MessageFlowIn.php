<?php

namespace Consilience\Laravel\MessageFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageFlowIn extends Model
{
    use HasFactory;

    protected $table = 'message_flow_in';

    /**
     * The default supported states for each message in the cache.
     * Other arbitrary states can be added.
     */
    public const STATUS_NEW = 'new';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAILED = 'failed';

    /**
     * Default values on creation.
     *
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'payload' => 'json',
    ];

    /**
     * Just guard the auto-generated properties.
     * Note: the UUID is sent to us.
     *
     * @var array
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

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
     * Set the payload as a JSON string.
     *
     * @param string $value
     * @return void
     */
    public function setJsonPayloadAttribute(string $value)
    {
        $this->attributes['payload'] = $value;
    }

    /**
     * Check if the record is new. Used by observer to confirm ready to process.
     *
     * @return boolean
     */
    public function isNew(): bool
    {
        return $this->status === static::STATUS_NEW;
    }

    /**
     * Mark the message as processed.
     *
     * @return self
     */
    public function setComplete()
    {
        $this->status = static::STATUS_COMPLETE;

        return $this;
    }

    /**
     * Mark the message as failed to processed.
     *
     * @return self
     */
    public function setFailed()
    {
        $this->status = static::STATUS_FAILED;

        return $this;
    }
}
