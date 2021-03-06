<?php

namespace App\Producer\Infrastructure\Messenger\AMQP\Declarer;

use PhpAmqpLib\Channel\AMQPChannel;

final class ExchangeDeclarer
{
    /** var string */
    private $exchangeType;

    /** var @bool */
    private $passive;

    /** var @bool */
    private $durable;

    /** var @bool */
    private $autoDelete;

    public function __construct($exchangeType, $passive, $durable, $autoDelete)
    {
        $this->exchangeType = $exchangeType;
        $this->passive = $passive;
        $this->durable = $durable;
        $this->autoDelete = $autoDelete;
    }

    public function declare(AMQPChannel $channel, string $exchangeName): void
    {
        $channel->exchange_declare(
            $exchangeName,
            $this->exchangeType,
            $this->passive,
            $this->durable,
            $this->autoDelete
        );
    }
}
