<?php

namespace App\Producer\Infrastructure\Ui\CLI;

use App\Producer\Infrastructure\Messenger\AMQP\ChannelManager;
use App\Producer\Infrastructure\Messenger\AMQP\Factory\MessageFactory;
use App\Producer\Infrastructure\Storage\Settings\SettingsReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SendSettings extends Command
{
    /** @var ChannelManager */
    private $channelManager;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var SettingsReader */
    private $settingsReader;

    /** @var string */
    private $exchangeName;

    public function __construct(
        ChannelManager $channelManager,
        MessageFactory $messageFactory,
        SettingsReader $settingsReader,
        string $exchangeName
    ) {
        $this->channelManager = $channelManager;
        $this->messageFactory = $messageFactory;
        $this->settingsReader = $settingsReader;
        $this->exchangeName = $exchangeName;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:settings:send')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $messagesToSend = $this->settingsReader->getMessages();
            $message = $this->messageFactory->create(json_encode(['messages' => $messagesToSend]));
            $this->channelManager->addMessage($message, $this->exchangeName);

            $output->writeln('Settings sent successfully');
        } catch (\Exception $e) {
            $output->writeln('Error:'.$e->getMessage());
        }
    }
}
