<?php

namespace Consilience\Laravel\MessageFlow\Messages;

/**
 * A message to send back to the source of an inbound message, so indicate
 * the original message was received.
 */

use JsonSerializable;

class AckMessage implements JsonSerializable
{
    protected $originalMessageUuid;
    protected $originalMessageName;

    public function __construct(string $originalMessageUuid, string $originalMessageName)
    {
        $this->originalMessageUuid = $originalMessageUuid;
        $this->originalMessageName = $originalMessageName;
    }

    public function toArray()
    {
        return [
            'originalMessageUuid' => $this->originalMessageUuid,
            'originalMessageName' => $this->originalMessageName,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray()
    }

    public function getOriginalMessageUuid()
    {
        return $this->originalMessageUuid;
    }

    public function getOriginalMessageName()
    {
        return $this->originalMessageName;
    }
}
