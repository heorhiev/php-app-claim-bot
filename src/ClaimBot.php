<?php

namespace app\claimBot;

use app\bot\Bot;
use app\claimBot\commands\{StartCommand, MessageCommand};


class ClaimBot extends Bot
{
    private static $_commands = [
        'start' => StartCommand::class,
    ];

    public static function getCommands(): array
    {
        return self::$_commands;
    }


    public function getTextHandler($text)
    {
        $class = parent::getTextHandler($text);

        if (!$class) {
            $class = MessageCommand::class;
        }

        return $class;
    }
}