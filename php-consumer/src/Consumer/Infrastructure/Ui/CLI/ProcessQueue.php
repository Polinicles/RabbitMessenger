<?php

namespace App\Consumer\Infrastructure\Ui\CLI;

use App\Consumer\Infrastructure\Messenger\AMQP\ChannelConsumer;
use App\Consumer\Infrastructure\Messenger\AMQP\ChannelManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueue extends Command
{
    /** @var ChannelManager */
    private $channelManager;

    /** @var ChannelConsumer */
    private $channelConsumer;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $messagesBatchSize;

    /** @var int */
    private $timeout;


    public function __construct(
        ChannelManager $channelManager,
        ChannelConsumer $channelConsumer,
        LoggerInterface $logger,
        int $timeout,
        string $messagesBatchSize
    ) {
        $this->channelManager = $channelManager;
        $this->channelConsumer = $channelConsumer;
        $this->logger = $logger;
        $this->timeout = $timeout;
        $this->messagesBatchSize = $messagesBatchSize;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:queue:process')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the queue')
            ->setDescription('Consume specific queue')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $channel = $this->channelManager->channel();
            $queueName = $input->getArgument('name');

            $this->channelManager->defineBatchSize($this->messagesBatchSize);
            $this->channelConsumer->consume($channel, $queueName, $this->callback());

            while (count($channel->callbacks) > 0) {
                $channel->wait(null, null, $this->timeout);
            }

            $output->writeln('Message/s received from ' . $queueName .', check the log');
            $this->channelManager->deleteQueue($queueName);

        } catch (\Exception $e) {
            $output->writeln('Error: '.$e->getMessage());
        }
    }

    private function callback()
    {
        return function ($message) {
            $this->logger->info('Message: '.$message->body);
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

            if ($message->body === 'quit') {
                $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
            }
        };
    }
}
