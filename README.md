
<!-- TOC -->

- [Laravel Message Flow](#laravel-message-flow)
    - [Background](#background)
    - [Messages](#messages)
    - [Reliability](#reliability)
    - [Flexibility](#flexibility)
    - [Configuration](#configuration)
- [Installation](#installation)
    - [Requirements](#requirements)
    - [Install Using Composer](#install-using-composer)
    - [Publish Migrations and Config](#publish-migrations-and-config)
    - [Example Configuration Using Redis](#example-configuration-using-redis)
    - [Sending an example message](#sending-an-example-message)

<!-- /TOC -->

# Laravel Message Flow

## Background

This package provides a pipe between two (or more) Laravel applications,
to send messages from a model on one application to a model on the
other application. It uses Laravel queues to pass the message across.
It attempts to do this robustly, reliably and flexibly.

If you can share a single queue database between Laravel applications,
then this package will support passing messages between these applications.

You can mix as many queue connections and queues as you like to route
the messages between multiple applications.

The use-case for this package is to replace a number of webhooks between
a suite of applications. The webhooks were becoming difficult to set
up, maintain and monitor. This package aims to tackle those problems.

## Messages

A message is any data or object that can be serialised into JSON in a
portable way. A portable way means it can be deserialised without
reference to any models or objects in the source application.
It's just data that can stand on its own.

Every message is given a UUID that gets carried across with it,
and is given a name that can be used for routing to specific queues
when sending, or routing to specific jobs when receiving.

## Reliability

This package ensures the message is dispatched to a queue.
Once dispatched, responsibility is handed over to the queuing broker
and it is considered sent. There is no end-to-end confirmation of receipt,
though that can be easily achieved with a simple message
in the opposite direction.

## Flexibility

To send a message, the payload is created as a `MessageFlowOut` model
instance. A pipeline of actions then routes that message to the correct
queue joining the two applications, and deletes it once it is safely
in the queue.

You do, however, have full control of that pipeline, and can add or
remove actions as necessary.
For example, you can remove the `DeleteCompleteMessage` action so queued
messages stay on the sender application for longer. You can add custom
routing rules, or maybe "tee" the messages into multiple destinations
(assuming that is not something you can do in the queue broker already).
You may simply want to put in additional logging.
The flexibility is there.

## Configuration

Here is an overview of the configuration steps:

1. On sender and receiver, create and configure a laravel queue that
   will be shared by both applications.
   An example configuration of a shared redis queue is given below,
   but any driver can be used.
2. Configure the Message Flow package to use the shared queue.
3. Create an observer to handle inbound messages on the receiver.

# Installation

## Requirements

- Laravel `8` or higher (laravel 7 is planned)
- PHP `7.4` or higher

This package is currently not registered on packagist.
Until it is, add this entry into your `composer.json` repositories block.

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/consilience/laravel-message-flow"
        }
    ]
}
```

## Install Using Composer

```bash
composer require consilience/laravel-message-flow
```

## Publish Migrations and Config

```
php artisan vendor:publish \
    --provider="Consilience\Laravel\MessageFlow\Providers\MessageFlowProvider"
```

You can then run `php artisan migrate` to migrate the database.

## Example Configuration Using Redis

We'll show an example of setting up the package for sender and receiver application with redis.
First, we need a queue connection that is shared between the sender and receiver applications.
We will use `redis` for te queue driver.

Laravel, by default, sets a prefix for all redis keys that is unique to the application.
This allows multiple applications to use a single redis database without keys clashing.
For our purposes, we want a prefix that *is* shared between applications.

The application-wide prefix added to redis keys is defined in `config/database.php`:

```
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),

            // Remove this default global prefix option:

            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        // ...
    ],
```

The `default` and the `cache` database entries will then need the prefix adding
to them. This will restore the prefix to prevent them clashing with other
applications using the same redis database:

`config/database.php`:

```
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),

            // Prefix needed here:

            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            
            // Prefix needed here:

            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
    ],
```

Now to add the shared connection.
This provides access to the redis database that both applications will share.

`config/database.php`:

```php
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        // ...

        // Give this connection any name other than your application name.
        // You may need to set different credentials if the shared redis
        // database is not the default database.

        'message-flow-database' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '0'),
            'prefix' => 'message-flow:',
        ],
    ],
```

The shared queue connection, using redis, should now be complete.
We can configure a queue using the shared connection:

`config/queue.php`:

```php
    'connections' => [
        // ...

        'message-flow-queue-connection' => [
            'driver' => 'redis',
            'connection' => 'message-flow-database',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
        ],
    ],
```

Now we configure *Message Flow* to use this connection and database.

`config/message-flow.php`:

```php
    'name-mappings' => [
        'default' => [
            'queue-connection' => 'message-flow-queue-connection',
            'queue-name' => 'message-flow',
        ],
    ],
```

Both the sending and receiving applications will have the same settings.
The receiving application will listen to the queue to handle the incoming
messages:

    php artisan queue:work message-flow-queue-connection --queue=message-flow

## Sending an example message

You can send a message simply by creating a new MessageFlowOut model from your sender application:

```php
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

MessageFlowOut::create(["payload" => ["data" => "test data here"]]);

MessageFlowOut::create(["payload" => $myModel]);
```

To retrieve the message from the receiver application, a listener can be
pointed at the inbound model:

```php
use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;

// TODO: listener example for MessageFlowIn
// TODO: mention the states
// TODO: names and routing (advanced)
// TODO: outbound pipeline (advanced)
```

