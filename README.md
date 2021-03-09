
# Laravel Message Flow

## Background

Simply, this package provides a pipe between two Laravel applications,
to send messages from a model on one application to a model on the
other application. It uses Laravel queues to pass the message across.
It attempts to do this robustly, reliably and flexibly.

If you can share a queue database between two Laravel applications,
then this package will support passing messages between these applications.

You can mix as many queue connections and queues as you like to route
the messages between multiple applications.

The use-case for this package is to replace a number of webhooks between
a suite of applications. The webhooks were becoming difficult to set
up, maintain and monitor. This aims to tackle those problems.

## Messages

A message is any data or object that can be serialised into JSON in a
portable way. A portable way means it can be deserialised without
reference to any models or objects in the source application.
It's just data that can stand on its own.

Every message is given a UUID that gets carried across with it,
and is given a name that cam be used for routing to specific queues
when sending, or routing to specific jobs when receiving.

## Reliability

This package ensures the message is dispatched to a queue.
Once dispatched, responsibility is handed over to the queuing broker
and it is considered sent. There is no end-to-end confirmation of receipt,
though that can be easily built around this solution with a simple message
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
The flexibilioty is there.

## Configuration

...

# Quickstart

...
