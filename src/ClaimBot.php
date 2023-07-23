<?php

namespace app\claimBot;

use app\bot\Bot;
use app\claimBot\commands\StartCommand;
use app\claimBot\commands\MessageCommand;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;


class ClaimBot extends Bot
{
    public function run(): void
    {
        $this->getBot()->command('start', function(Message $message) {
            (new StartCommand($this, $message))->run();
        });

        //Handle text messages
        $this->getBot()->on(function (\TelegramBot\Api\Types\Update $update) {
            (new MessageCommand($this, $update->getMessage()))->run();
        }, function () {
            return true;
        });

        $this->getBot()->run();
    }

    public static function getCommands(): array
    {
        return [];
    }
}