<?php

namespace Tests\Unit\Producer\Infrastructure\Messenger\Factory;

use App\Producer\Infrastructure\Messenger\AMQP\Factory\QueueFactory;
use PHPUnit\Framework\TestCase;

class QueueFactoryTest extends TestCase
{
    public function test_it_will_create_necessary_queues()
    {
        $messagesPerQueue = 10000;
        $messagesToSend = 100000;
        $amountOfQueues = 10;

        $queues = [];
        for ($i = 1; $i <= $amountOfQueues; $i++) {
            array_push($queues, 'queue'.$i);
        }

        $queueFactory = new QueueFactory($messagesPerQueue);
        $necessaryQueues = $queueFactory->calculateNecessaryQueues($messagesToSend);

        static::assertSame($queues, $necessaryQueues);
    }
}
