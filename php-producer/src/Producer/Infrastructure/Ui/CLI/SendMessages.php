<?php

namespace App\Producer\Infrastructure\Ui\CLI;

use App\Producer\Infrastructure\Messenger\AMQP\ChannelManager;
use App\Producer\Infrastructure\Messenger\AMQP\Factory\MessageFactory;
use App\Producer\Infrastructure\Storage\Settings\SettingsReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SendMessages extends Command
{
    /** @var ChannelManager */
    private $channelManager;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var SettingsReader */
    private $settingsReader;

    /** @var string */
    private $exchangeName;

    /** @var int */
    private $messagesBatchSize;

    public function __construct(
        ChannelManager $channelManager,
        MessageFactory $messageFactory,
        SettingsReader $settingsReader,
        string $exchangeName,
        int $messagesBatchSize
    ) {
        $this->channelManager = $channelManager;
        $this->messageFactory = $messageFactory;
        $this->settingsReader = $settingsReader;
        $this->exchangeName = $exchangeName;
        $this->messagesBatchSize = $messagesBatchSize;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:message:send')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messagesToSend = $this->settingsReader->getMessages();

        try{
            $message = $this->messageFactory->create('First message');
            $this->channelManager->addMessageToBatch($message, $this->exchangeName);

            for ($i = 1; $i <= $messagesToSend; $i++) {
                $message->setBody('id:' . $i . '|text: white walkers coming!|to: jhonsnow@winterfell.com');
                $this->channelManager->addMessageToBatch($message, $this->exchangeName);

                if (($i+1) % $this->messagesBatchSize === 0){
                    $this->channelManager->addMessageToBatch($message, $this->exchangeName);
                    $this->channelManager->publishBatch();
                }
            }

            $message->setBody('quit');
            $this->channelManager->addMessageToBatch($message, $this->exchangeName);
            $this->channelManager->publishBatch();
            $this->channelManager->close();

            $output->writeln('Message/s sent successfully');
        } catch (\Exception $e) {
            $output->writeln('Error:' . $e->getMessage());
        }
    }
}
