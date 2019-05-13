<?php

namespace App\Producer\Infrastructure\Messenger\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

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
