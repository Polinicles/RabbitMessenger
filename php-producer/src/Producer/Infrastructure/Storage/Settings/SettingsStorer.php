<?php

namespace App\Producer\Infrastructure\Storage\Settings;

final class SettingsStorer extends Settings
{
    const OPEN_MODE = 'wb';

    /** @var array */
    private $content;

    public function __construct()
    {
        $this->content = [];
    }

    public function defineMessages(int $messages): void
    {
        $this->content['messages'] = $messages;
    }

    public function defineQueues(array $queues): void
    {
        $this->content['queues'] = $queues;
    }

    public function saveSettings(): void
    {
        $fp = fopen(self::REPORT_FILE, self::OPEN_MODE);
        fwrite($fp, json_encode($this->content()));
        fclose($fp);
    }

    private function content(): array
    {
        return $this->content;
    }
}
