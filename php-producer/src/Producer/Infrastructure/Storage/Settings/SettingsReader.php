<?php

namespace App\Producer\Infrastructure\Storage\Settings;

class SettingsReader extends Settings
{
    const MESSAGES = 'messages';

    public function getMessages(): int
    {
        $messages = $this->getFileContent()[self::MESSAGES];

        return (int) $messages;
    }

    private function getFileContent(): array
    {
        $content = file_get_contents(self::REPORT_FILE);
        $currentSettings = json_decode($content, true);

        return $currentSettings;
    }
}
