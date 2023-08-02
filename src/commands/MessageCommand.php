<?php

namespace app\claimBot\commands;

use app\claimBot\constants\ClaimBotConst;
use app\claimBot\entities\Contact;
use app\googleSheet\config\GoogleSheetDto;
use app\googleSheet\GoogleSheet;
use app\toolkit\components\validators\PhoneValidator;
use app\toolkit\components\validators\TextValidator;
use app\toolkit\services\LoggerService;
use app\toolkit\services\SettingsService;


class MessageCommand extends \app\bot\models\Command
{
    /** @var Contact */
    private $_contact;


    public function run(): void
    {
        if ($this->getSendedContact() != null) {
            $this->enterPhone();
        } else {
            $this->{$this->getContact()->step}();
        }
    }


    public function enterName(): void
    {
        $text = trim($this->getBot()->getIncomeMessage()->getText());
        $text = preg_replace('/[^a-zA-ZА-Яа-я0-9-\s$]/u', '', $text);

        $message = $this->getBot()->getNewMessage();

        if ((new TextValidator())->isValid($text)) {
            $step = ClaimBotConst::STEP_ENTER_PHONE;

            $sendPhoneButton = array_merge($this->getBot()->getOptions()->buttons['sendPhone'], [
                'request_contact' => true
            ]);

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
                [[$sendPhoneButton]],
                true,
                true,
                true
            );

            $message->setKeyboardMarkup($keyboard);

            $this->getContact()->update(['name' => $text, 'step' => $step]);
        } else {
            $step = $this->getContact()->step . '.error';
        }

        $message = $message->setMessageView($step)->setAttributes([
            'userName' => $text,
            'contact' => $this->getContact(),
        ]);
        
        $this->getBot()->sendMessage($message);
    }


    public function enterPhone(): void
    {
        if ($this->getSendedContact() != null) {
            $text = $this->getSendedContact()->getPhoneNumber();
        } else {
            $text = trim($this->getBot()->getIncomeMessage()->getText());
        }

        $message = $this->getBot()->getNewMessage();

        if ((new PhoneValidator())->isValid($text)) {
            $step = ClaimBotConst::STEP_FINALE;

            $this->getContact()->update([
                'phone' => $text,
                'step' => $step,
                'status' => ClaimBotConst::CONTACT_STATUS_FINALE,
            ]);

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
                [
                    $this->getBot()->getOptions()->buttons['finale']
                ]
            ]);

            $message->setKeyboardMarkup($keyboard);
        } else {
            $step = $this->getContact()->step . '.error';
        }

        $message->setMessageView($step)->setAttributes([
            'phone' => $text,
            'contact' => $this->getContact(),
        ]);

        $this->getBot()->sendMessage($message);

        $googleSheet = new GoogleSheet(
            SettingsService::load('claim/google_sheet', GoogleSheetDto::class)
        );

        $data  = $this->getContact()->getAttributes(['id', 'name', 'phone']);
        $data[] = date('Y-m-d H:i:s');

        $googleSheet->save([$data]);
    }


    public function finale()
    {
        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
            [
                $this->getBot()->getOptions()->buttons['finale']
            ]
        ]);

        $message = $this->getBot()->getNewMessage();

        $message->setKeyboardMarkup($keyboard)->setMessageView(ClaimBotConst::STEP_FINALE)->setAttributes([
            'contact' => $this->getContact(),
            'phone' => $this->getContact()->phone,
        ]);

        $this->getBot()->sendMessage($message);
    }


    private function getContact(): ?Contact
    {
        if ($this->_contact === null) {
            $this->_contact = Contact::repository()->findById($this->getUserId())->asEntityOne();
        }

        return $this->_contact;
    }


    private function getSendedContact()
    {
        return $this->getBot()->getDataFromRequest()->getMessage()->getContact();
    }
}