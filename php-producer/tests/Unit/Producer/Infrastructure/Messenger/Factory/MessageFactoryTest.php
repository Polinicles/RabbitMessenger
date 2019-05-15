<?php

namespace Tests\Unit\Producer\Infrastructure\Messenger\Factory;

use App\Producer\Infrastructure\Messenger\AMQP\Factory\MessageFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class MessageFactoryTest extends TestCase
{
    public function test_it_should_create_message()
    {
        $faker = Factory::create();
        $messageBody = $faker->text;
        $contentType = 'text/plain';
        $deliveryMode = 2;

        $messageFactory = new MessageFactory($contentType, $deliveryMode);
        $newMessage = $messageFactory->create($messageBody);

        static::assertSame($messageBody, $newMessage->getBody());
        static::assertSame($contentType, $newMessage->get('content_type'));
        static::assertSame($deliveryMode, $newMessage->get('delivery_mode'));
    }
}
