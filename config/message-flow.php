<?php

use Consilience\Laravel\MessageFlow\Pipes\RouteFromConfig;
use Consilience\Laravel\MessageFlow\Pipes\QueueMessage;
use Consilience\Laravel\MessageFlow\Pipes\CompleteQueuedMessage;
use Consilience\Laravel\MessageFlow\Pipes\DeleteCompleteMessage;

return [

    // Handing outbound messages.

    'out' => [
        // The pipeline to process a new outbound message.

        'routing-pipeline' => [
            RouteFromConfig::class,
            QueueMessage::class,
            CompleteQueuedMessage::class,
            //DeleteCompleteMessage::class,
        ],

        // Mappings message names to queues.

        // The fallback-mapping is for any queue-connection or queue-name
        // for a message name that is not set (undefined or null).
        // A null fallback-mapping will use the system default.

        'fallback-mapping' => [
            'queue-connection' => null,
            'queue-name' => null,
        ],

        // Each message name can map to a queue connection and queue name.

        'name-mappings' => [
            'default' => [
                'queue-connection' => null,
                'queue-name' => null,
            ],
            'foo-example' => [
                'queue-connection' => 'redis',
                'queue-name' => 'distributed:queue',
            ],
        ],

        // List of message names that are set to `complete` immediately
        // they are `queued`.
        // Needs the `CompleteQueuedMessage` pipe.

        // CHECKME: do the opposite?

        'complete-on-queued' => [
            '*',
        ],
    ],

    // Handling inbound messages.

    'in' => [
        // Inbound messages that need to be acknowledged with a return message.
        // This maps the original inbound message name to the name of the
        // ack message response.

        'ack-required' => [
            'my-special-message' => [
                'name' => 'my-special-message-acknowledgement',
            ],
            // `*` matches anything.
            // `{name}` is the original message name.
            'msg-*' => [
                'template' => '{name}-ack',
            ],
        ],
    ],
];
