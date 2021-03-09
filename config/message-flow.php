<?php

use Consilience\Laravel\MessageFlow\Pipes\RouteFromConfig;
use Consilience\Laravel\MessageFlow\Pipes\QueueMessage;
use Consilience\Laravel\MessageFlow\Pipes\DeleteCompleteMessage;

return [

    // Outbound messages.

    'out' => [
        // The pipeline to take a new outbound message through.

        'routing-pipeline' => [
            RouteFromConfig::class,
            QueueMessage::class,
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
    ],
];
