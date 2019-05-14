<?php

namespace App\Producer\Infrastructure\Messenger\AMQP\Factory;

final class QueueFactory
{
    const BASE_QUEUE_NAME = 'queue';

    /** @var int */
    private $maxMessagesPerQueue;

    public function __construct(int $maxMessagesPerQueue)
    {
        $this->maxMessagesPerQueue = $maxMessagesPerQueue;
    }

    public function calculateNecessaryQueues(int $messagesToSend): array
    {
        $queues = [];
        $limit = $this->maxMessagesPerQueue;
        $counter = 1;

        if ($messagesToSend <= $limit) {
            return [self::BASE_QUEUE_NAME . $counter];
        }

        while ($messagesToSend > 0) {
            $queueName = self::BASE_QUEUE_NAME . $counter;
            array_push($queues, $queueName);
            $counter++;
            $messagesToSend -= $limit;
        }

        return $queues;
    }
}
