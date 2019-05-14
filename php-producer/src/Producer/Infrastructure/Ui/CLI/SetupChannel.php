<?php

namespace App\Producer\Infrastructure\Ui\CLI;

use App\Producer\Infrastructure\Messenger\AMQP\ChannelManager;
use App\Producer\Infrastructure\Messenger\AMQP\Declarer\ExchangeDeclarer;
use App\Producer\Infrastructure\Messenger\AMQP\Declarer\QueueDeclarer;
use App\Producer\Infrastructure\Messenger\AMQP\Factory\QueueFactory;
use App\Producer\Infrastructure\Storage\Settings\SettingsStorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SetupChannel extends Command
{
    const MSG_TO_BE_SENT = 'messages';

    const DEFAULT_MSG_TO_SEND = 10;

    /** @var ChannelManager */
    private $channelManager;

    /** @var QueueDeclarer */
    private $queueManager;

    /** @var QueueFactory */
    private $queueFactory;

    /** @var ExchangeDeclarer */
    private $exchangeDeclarer;

    /** @var SettingsStorer */
    private $settingsStorer;

    /** @var string */
    private $exchangeName;

    public function __construct(
        ChannelManager $channelManager,
        QueueDeclarer $queueManager,
        QueueFactory $queueFactory,
        ExchangeDeclarer $exchangeDeclarer,
        string $exchangeName
    ) {
        $this->channelManager = $channelManager;
        $this->queueManager = $queueManager;
        $this->queueFactory = $queueFactory;
        $this->exchangeDeclarer = $exchangeDeclarer;
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $messagesToSend = (int) $input->getOption(self::MSG_TO_BE_SENT);
            $this->settingsStorer->defineMessages($messagesToSend);
            $this->settingsStorer->saveSettings();

            $channel = $this->channelManager->channel();
            $exchange = $this->exchangeName;
            $queues = $this->queueFactory->calculateNecessaryQueues($messagesToSend);
            $this->exchangeDeclarer->declare($channel, $exchange);

            foreach ($queues as $queue) {
                $this->queueManager->declare($channel, $queue);
                $this->channelManager->bindQueueToExchange($queue, $exchange);
            }

            $this->channelManager->close();

            $output->writeln('Channel defined successfully');
        } catch (\Throwable $exception) {
            $output->writeln('Error: '.$exception->getMessage());
        }
    }
}
