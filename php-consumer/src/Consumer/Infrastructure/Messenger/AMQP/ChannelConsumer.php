<?php

namespace App\Consumer\Infrastructure\Messenger\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;

final class ChannelConsumer
{
    /** @var string */
    private $consumerTag;

    /** @var bool */
    private $noLocal;

    /** @var bool */
    private $noAck;

    /** @var bool */
    private $exclusive;

    /** @var bool */
    private $noWait;

    public function __construct(string $consumerTag, bool $noLocal, bool $noAck, bool $exclusive, bool $noWait)
    {
        $this->consumerTag = $consumerTag;
        $this->noLocal = $noLocal;
        $this->noAck = $noAck;
        $this->exclusive = $exclusive;
        $this->noWait = $noWait;
    }

    public function consume(AMQPChannel $channel, string $queue, $callback): void
    {
        $channel->basic_consume(
            $queue,
            $this->consumerTag,
            $this->noLocal,
            $this->noAck,
            $this->exclusive,
            $this->noWait,
            $callback
        );
    }
}
