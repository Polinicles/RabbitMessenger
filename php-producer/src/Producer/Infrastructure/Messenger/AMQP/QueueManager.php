<?php

namespace App\Producer\Infrastructure\Messenger\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;

class QueueManager
{
    /** var @bool */
    private $passive;

    /** var @bool */
    private $durable;

    /** var @bool */
    private $exclusive;

    /** var @bool */
    private $autoDelete;

    public function __construct(bool $passive, bool $durable, bool $exclusive, bool $autoDelete)
    {
        $this->passive = $passive;
        $this->durable = $durable;
        $this->exclusive = $exclusive;
        $this->autoDelete = $autoDelete;
    }

    public function declare(AMQPChannel $channel, string $queueName)
    {
        $channel->queue_declare($queueName, $this->passive, $this->durable, $this->exclusive, $this->autoDelete);
    }
}
