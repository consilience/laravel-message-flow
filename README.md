
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

...

# Quickstart

## Installation


Requirements:

- Laravel `7` or higher  
- PHP `7.4` or higher


This package is currently not registered on packagist, so add this repository into your composer.json repositories block.

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


Via Composer

```bash
composer require consilience/laravel-message-flow
```


Publish Migration and Config

`php artisan vendor:publish --provider="Consilience\Laravel\MessageFlow\Providers\MessageFlowProvider"`

and run the migration `php artisan migrate`

We'll show an example of setting up the package for sender and receiver application with redis.
Firstly, we need to create a connection to redis database that is shared between the sender and receiver applications.

By default, Laravel config would prepend the redis connection with a prefix derived from the application name.
Since the sender and receiver applications would have different application names, we need to remove this prefix.


go to `config/database.php` and remove or comment out the prefix from the global redis option, 
and copy them to the individual redis connection instead:
```
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            // Comment or remove this global prefix option
            // 'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            // Add the prefix here
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            // And here
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
    ],
```

Next, we want to create a new redis connection for the message flow:
```php
    // you can give this any name other than your application name
    'messenger' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '2'),
    ],
```

Next, you need to map the new redis connection to the message-flow config to indicate that as the connection to use.
Go to `config/message-flow.php` and set:

```php
    'name-mappings' => [
        'default' => [
            'queue-connection' => 'redis',
            'queue-name' => 'messenger',
        ],
    ],
```

Lastly, run your redis queue worker to listen on the defined queue:
`php artisan queue:work redis --queue=messenger`

## Sending an example message

You can send a message simply by creating a new MessageFlowOut model from your sender application:
`Consilience\Laravel\MessageFlow\Models\MessageFlowOut::create(["payload" => ["data" => "test data here"]]);`

To retrieve the message from the receiver application:
`Consilience\Laravel\MessageFlow\Models\MessageFlowIn::all()`

