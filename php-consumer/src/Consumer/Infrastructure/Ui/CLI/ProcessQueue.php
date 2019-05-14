<?php

namespace App\Consumer\Infrastructure\Ui\CLI;

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

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $messagesBatchSize;

    public function __construct(ChannelManager $channelManager, LoggerInterface $logger, string $messagesBatchSize)
    {
        $this->channelManager = $channelManager;
        $this->logger = $logger;
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

            $callback = function ($msg) {
                $this->logger->notice('Message: '.$msg->body);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            };
            $this->channelManager->defineBatchSize($this->messagesBatchSize);
            $channel->basic_consume($queueName, '', false, false, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }
        }catch (\Exception $e) {
            $this->channelManager->closeConnection();
        }

        //TODO: delete queues if all msg have been sent
        $output->writeln('Message/s received, check the log');

    }
}
