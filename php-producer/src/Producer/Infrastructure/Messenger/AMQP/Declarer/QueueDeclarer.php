<?php

namespace App\Producer\Infrastructure\Messenger\AMQP\Declarer;

use PhpAmqpLib\Channel\AMQPChannel;

final class QueueDeclarer
{
    /** var @bool */
    private $passive;

    /** var @bool */
    private $durable;

    /** var @bool */
    private $exclusive;

    /** var @bool */
    private $autoDelete;

    public function __construct(
        bool $passive,
        bool $durable,
        bool $exclusive,
        bool $autoDelete
    ) {
        $this->passive = $passive;
        $this->durable = $durable;
        $this->exclusive = $exclusive;
        $this->autoDelete = $autoDelete;
    }

    public function declare(AMQPChannel $channel, string $queueName): void
    {
        $channel->queue_declare($queueName, $this->passive, $this->durable, $this->exclusive, $this->autoDelete);
    }
}
