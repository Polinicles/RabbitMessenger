<?php

namespace App\Consumer\Infrastructure\Ui\CLI;

use App\Consumer\Infrastructure\Messenger\AMQP\ChannelConsumer;
use App\Consumer\Infrastructure\Messenger\AMQP\ChannelManager;
use App\Consumer\Infrastructure\Messenger\AMQP\Factory\QueueFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumeMessages extends Command
{
    const MSG_TO_BE_SENT = 'messages';

    const DEFAULT_MSG_TO_SEND = 10;

    const QUEUE_PROCESSOR = 'app:queue:process';

    /** @var ChannelManager */
    private $channelManager;

    /** @var ChannelConsumer */
    private $channelConsumer;

    /** @var QueueFactory */
    private $queueFactory;

    public function __construct(
        ChannelManager $channelManager,
        ChannelConsumer $channelConsumer,
        QueueFactory $queueFactory
    ) {
        $this->channelManager = $channelManager;
        $this->channelConsumer = $channelConsumer;
        $this->queueFactory = $queueFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:message:consume')
            ->addOption(
                self::MSG_TO_BE_SENT,
                null,
                InputOption::VALUE_REQUIRED,
                'How many messages do you want to send?',
                self::DEFAULT_MSG_TO_SEND
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $messagesToBeReceived = (int) $input->getOption(self::MSG_TO_BE_SENT);

            $queues = $this->queueFactory->calculateNecessaryQueues($messagesToBeReceived);
            $ProcessQueueCommand = $this->getConsumerCommand();

            foreach ($queues as $queue) {
                $arguments = [
                    'command' => self::QUEUE_PROCESSOR,
                    'name'    => $queue
                ];
                $queueArgument = new ArrayInput($arguments);
                $ProcessQueueCommand->run($queueArgument, $output);
            }

            $output->writeln('Message/s received successfully');
        } catch (\Exception $e) {
            $output->writeln('Error: '.$e->getMessage());
        }
    }

    private function getConsumerCommand(): Command
    {
        return $this->getApplication()->find(self::QUEUE_PROCESSOR);
    }
}
