<?php

namespace App\Producer\Infrastructure\Messenger\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class ChannelManager
{
    /** @var AMQPStreamConnection */
    private $connection;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
    }

    public function channel(): AMQPChannel
    {
        return $this->connection->channel();
    }

    public function bindQueueToExchange(string $queueName, string $exchangeName)
    {
        $channel = $this->channel();
        $channel->queue_bind($queueName, $exchangeName);
    }

    public function addMessageToBatch(AMQPMessage $message, string $exchangeName)
    {
        $channel = $this->channel();
        $channel->batch_basic_publish($message, $exchangeName);
    }

    public function publishBatch()
    {
        $channel = $this->channel();
        $channel->publish_batch();
    }

    /**
     * @throws \Exception
     */
    public function close(): void
    {
        $channel = $this->channel();
        $channel->close();
        $this->connection->close();
    }
}
