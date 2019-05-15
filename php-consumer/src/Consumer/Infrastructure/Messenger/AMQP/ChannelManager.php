<?php

namespace App\Consumer\Infrastructure\Messenger\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

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

    public function defineBatchSize(int $size): void
    {
        $this->channel->basic_qos(null, $size, null);
    }

    public function deleteQueue(string $queue): void
    {
        $this->channel->queue_delete($queue);
    }

    /**
     * @throws \Exception
     */
    public function close(): void
    {
        $this->channel->close();
    }
}
