<?php

namespace Consilience\Laravel\MessageFlow\Messages;

/**
 * A message to send back to the source of an inbound message, so indicate
 * the original message was received.
 */

use JsonSerializable;

class AckMessage implements JsonSerializable
{
    public function __construct(
        protected string $originalMessageUuid,
        protected string $originalMessageName,
    ) {}

    public function toArray(): array
    {
        return [
            'originalMessageUuid' => $this->originalMessageUuid,
            'originalMessageName' => $this->originalMessageName,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function getOriginalMessageUuid(): string
    {
        return $this->originalMessageUuid;
    }

    public function getOriginalMessageName(): string
    {
        return $this->originalMessageName;
    }
}