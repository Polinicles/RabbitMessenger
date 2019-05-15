# RabbitMessenger

## Quick guide

### Requirements

* docker https://www.docker.com/
* docker-compose https://docs.docker.com/compose/

### Get started

Clone the project: 
```sh
$ git clone git@github.com:Polinicles/RabbitMessenger.git
$ cd RabbitMessenger
```

### Define .env

In order to make this app work, it requires having an instance of [Cloud AMQP](https://www.cloudamqp.com/). After creating the new instance, some parameters of it need to be specified in a new ```.env``` file placed in the root directory

```
CLOUDAMQP_HOST=
CLOUDAMQP_PORT=5672
CLOUDAMQP_USER=
CLOUDAMQP_PASS=
CLOUDAMQP_VHOST=

MAX_MSG_QUEUE=1000
MSG_BATCH_AMOUNT=50
EXCHANGE=testExchange
```

### Docker

Start the project

```sh
$ docker-compose up -d
```

Install dependencies

```sh
$ docker-compose exec php_producer composer install
$ docker-compose exec php_consumer composer install
```

## Application

### Approach

There're two **PHP** docker containers, one it's created for sending the messages (mail and message). On the other hand, the second container will receive / consume every message and will log the content. 

### Send Messages

In order to send the messages, connect to the producer container

```sh
$ docker-compose exec php_producer bash
```

Execute the command that will setup the channel by creating the necessary queues and specify the amount of messages you want to send

```sh
$ bin/console app:channel:setup --messages={number-of-msg-to-be-sent}
```

Once the exchange and queues are defined, it's time to send the messages using:

```sh
$ bin/console app:message:send
```

The messages will be sent in batches to improve effiency. Remember that the amount of messages per queue and for batch can be defined in the ```.env```

### Receive Messages

In order to send the messages, connect to the producer container

```sh
$ docker-compose exec php_consumer bash
```

The container in charge of consuming the messages will receive them by using

```sh
$ bin/console app:message:consume --messages={number-of-msg-to-be-received}
```

That will execute as many daemons as queues are, will process the messages by logging the content and will destroy the queues when they're finished.

Enjoy =)
