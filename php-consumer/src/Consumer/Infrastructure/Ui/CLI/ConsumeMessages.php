<?php

namespace App\Consumer\Infrastructure\Ui\CLI;

use App\Consumer\Infrastructure\Messenger\AMQP\ChannelManager;
use App\Consumer\Infrastructure\Messenger\AMQP\Factory\QueueFactory;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeMessages extends Command
{
    /** @var ChannelManager */
    private $channelManager;

    /** @var QueueFactory */
    private $queueFactory;

    public function __construct(
        ChannelManager $channelManager,
        QueueFactory $queueFactory
    ) {
        $this->channelManager = $channelManager;
        $this->queueFactory = $queueFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:message:consume')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $channel = $this->channelManager->channel();
            $messagesToBeReceived = 0;
            $callback = function ($msg) use (&$messagesToBeReceived) {
                dump('hi');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                $settings = json_decode($msg->body, true);dump($settings);
                $messagesToBeReceived = $settings['messages'];
                if(!$messagesToBeReceived) {
                    throw new \Exception('Missing messages amount');
                } else {
                    $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
                }
            };
            $channel->basic_consume('queue1', '', false, false, false, false, $callback); //TODO: fix this
            while (count($channel->callbacks)) {
                $channel->wait(null, false, 10);
            }

            $queues = $this->queueFactory->calculateNecessaryQueues($messagesToBeReceived);
            $ProcessQueueCommand = $this->getApplication()->find('app:queue:process');


            foreach ($queues as $queue) {
                $arguments = [
                    'command' => 'app:queue:process',
                    'name'    => $queue
                ];
                $queueArgument = new ArrayInput($arguments);
                $ProcessQueueCommand->run($queueArgument, $output);
            }

            $output->writeln('Message/s sent successfully');
        } catch (\Exception $e) {
            $output->writeln('Error:'.$e->getMessage());
        }
    }
}
