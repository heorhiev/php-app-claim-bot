<?php

namespace app\common\bots\vacancy;

use app\common\components\Bot;
use app\common\bots\vacancy\commands\StartCommand;
use app\common\bots\vacancy\commands\MessageCommand;
use app\common\services\RenderService;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;


class VacancyBot extends Bot
{
    /** @var $_bot Client */
    private $_bot;
    private $_inlineKeyboardMarkup;
    private $_replyKeyboardMarkup;


    public function handler(): void
    {
        $this->_bot = new Client($this->_options->vacancyBotToken);

        $this->_bot->command('start', function(Message $message) {
            (new StartCommand($this, $message))->run();
        });

        //Handle text messages
        $this->_bot->on(function (\TelegramBot\Api\Types\Update $update) {
            (new MessageCommand($this, $update->getMessage()))->run();
        }, function () {
            return true;
        });

        $this->_bot->run();
    }


    public function setInlineKeyboardMarkup($inlineKeyboardMarkup): self
    {
        $this->_inlineKeyboardMarkup = $inlineKeyboardMarkup;
        return $this;
    }


    public function setReplyKeyboardMarkup($replyKeyboardMarkup): self
    {
        $this->_replyKeyboardMarkup = $replyKeyboardMarkup;
        return $this;
    }


    public function sendMessage($userId, $messageKey, $message = null, array $attributes = [])
    {
        if (empty($message)) {
            $userLang = null;
            $message = $this->getViewContent($messageKey, $attributes, $userLang);
        }

        $keyboard = null;

        if ($this->_inlineKeyboardMarkup) {
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($this->_inlineKeyboardMarkup);
        }

        if ($this->_replyKeyboardMarkup) {
            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($this->_replyKeyboardMarkup, true, false, true);
        }

        $this->_bot->sendMessage($userId, $message, 'html', false, null, $keyboard);
    }


    private function getViewContent($messageKey, $attributes, $lang = null): ?string
    {
        $path = COMMON_PATH . '/bots/vacancy/views/' . $messageKey;

        if ($lang) {
            $langPath = $path . '.' . $lang;

            if (RenderService::exists($langPath)) {
                $path = $langPath;
            }
        }

        return RenderService::get($path, $attributes);
    }
}