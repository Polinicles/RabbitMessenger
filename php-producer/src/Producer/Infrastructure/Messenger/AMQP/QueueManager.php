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

    /** @var int */
    private $maxMessagesPerQueue;

    public function __construct(
        bool $passive,
        bool $durable,
        bool $exclusive,
        bool $autoDelete,
        int $maxMessagesPerQueue

    ) {
        $this->passive = $passive;
        $this->durable = $durable;
        $this->exclusive = $exclusive;
        $this->autoDelete = $autoDelete;
        $this->maxMessagesPerQueue = $maxMessagesPerQueue;
    }

    public function declare(AMQPChannel $channel, string $queueName)
    {
        $channel->queue_declare($queueName, $this->passive, $this->durable, $this->exclusive, $this->autoDelete);
    }

    public function calculateNecessaryQueues(int $messagesToSend): array
    {
        $queues = [];
        $limit = $this->maxMessagesPerQueue;
        $counter = 1;

        if ($messagesToSend <= $limit) {
            return ['queue'.$counter];
        }

        while ($messagesToSend > 0) {
            $queueName = 'queue'.$counter;
            array_push($queues, $queueName);
            $counter++;
            $messagesToSend -= $limit;
        }

        return $queues;
    }
}
