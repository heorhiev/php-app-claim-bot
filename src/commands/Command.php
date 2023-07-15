<?php

namespace app\common\bots\vacancy\commands;

use app\common\bots\vacancy\entities\Contact;
use app\common\bots\vacancy\VacancyBot;
use TelegramBot\Api\Types\Message;


abstract class Command
{
    protected $_bot;
    protected $_message;

    private $_contact;


    abstract public function run(): void;


    public function __construct(VacancyBot $bot, Message $message)
    {
        $this->_bot = $bot;
        $this->_message = $message;
    }


    public function getBot(): VacancyBot
    {
        return $this->_bot;
    }


    public function getMessage(): Message
    {
        return $this->_message;
    }


    public function getUserId(): int
    {
        return $this->getMessage()->getChat()->getId();
    }


    public function getContact(): ?Contact
    {
        if ($this->_contact === null) {
            $this->_contact = Contact::repository()->findById($this->getUserId())->asEntityOne();
        }

        return $this->_contact;
    }
}