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

        'fallback-mapping' => [
            'queue-connection' => null,
            'queue-name' => null,
        ],

        'name-mappings' => [
            'default' => [
                'queue-connection' => null,
                'queue-name' => null,
            ],
            'foo' => [
                'queue-connection' => 'redis',
                'queue-name' => 'distributed',
            ],
        ],
    ],
];
