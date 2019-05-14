<?php

namespace App\Producer\Infrastructure\Messenger\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class ChannelManager
{
    /** @var AMQPChannel */
    private $channel;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->channel = $connection->channel();
    }

    public function channel(): AMQPChannel
    {
        return $this->channel;
    }

    public function bindQueueToExchange(string $queueName, string $exchangeName)
    {
        $this->channel->queue_bind($queueName, $exchangeName);
    }

    public function addMessageToBatch(AMQPMessage $message, string $exchangeName)
    {
        $this->channel->batch_basic_publish($message, $exchangeName);
    }

    public function publishBatch()
    {
        $this->channel->publish_batch();
    }

    /**
     * @throws \Exception
     */
    public function close(): void
    {
        $this->channel->close();
    }
}
