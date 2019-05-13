<?php

namespace App\Producer\Infrastructure\Ui\CLI;

use App\Producer\Infrastructure\Messenger\AMQP\ChannelManager;
use App\Producer\Infrastructure\Messenger\AMQP\ExchangeManager;
use App\Producer\Infrastructure\Messenger\AMQP\QueueManager;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetupChannel extends Command
{
    const MSG_TO_BE_SENT = 'messages';

    const DEFAULT_MSG_TO_SEND = 10;

    /** @var ChannelManager */
    private $channelManager;

    /** @var QueueManager */
    private $queueManager;

    /** @var ExchangeManager */
    private $exchangeManager;

    /** @var string */
    private $exchangeName;

    public function __construct(
        ChannelManager $channelManager,
        QueueManager $queueManager,
        ExchangeManager $exchangeManager,
        string $exchangeName
    ) {
        $this->channelManager = $channelManager;
        $this->queueManager = $queueManager;
        $this->exchangeManager = $exchangeManager;
        $this->exchangeName = $exchangeName;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:channel:setup')
            ->addOption(
                self::MSG_TO_BE_SENT,
                null,
                InputOption::VALUE_REQUIRED,
                'How many messages do you want to send?',
                self::DEFAULT_MSG_TO_SEND
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) //TODO: implement try & catch
    {
        $messagesToSend = $input->getOption(self::MSG_TO_BE_SENT);

        $queues = $this->queueManager->calculateNecessaryQueues($messagesToSend);
        $channel = $this->channelManager->channel();
        $exchange = $this->exchangeName;
        $this->exchangeManager->declare($channel, $exchange);

        foreach ($queues as $queue) {
            $this->queueManager->declare($channel, $queue);
            $this->channelManager->bindQueueToExchange($queue, $exchange);
        }

        $this->channelManager->close();

        $output->writeln('Channel defined successfully');
    }
}
