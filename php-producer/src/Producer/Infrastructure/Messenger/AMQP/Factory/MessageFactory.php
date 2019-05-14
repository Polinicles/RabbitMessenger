<?php

namespace App\Producer\Infrastructure\Messenger\AMQP\Factory;

use PhpAmqpLib\Message\AMQPMessage;

final class MessageFactory
{
    /** @var string */
    private $contentType;

    /** @var int */
    private $deliveryMode;

    public function __construct(string $contentType, int $deliveryMode)
    {
        $this->contentType = $contentType;
        $this->deliveryMode = $deliveryMode;
    }

    public function create(string $body): AMQPMessage
    {
        return new AMQPMessage(
            $body,
            [
              'content_type' => $this->contentType,
              'delivery_mode' => $this->deliveryMode
            ]
        );
    }
}
