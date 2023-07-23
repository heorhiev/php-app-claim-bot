<?php

namespace app\claimBot\commands;

use app\claimBot\constants\ClaimBotConst;
use app\claimBot\entities\Contact;
use app\googleSheet\config\GoogleSheetDto;
use app\googleSheet\GoogleSheet;
use app\toolkit\components\validators\PhoneValidator;
use app\toolkit\components\validators\TextValidator;
use app\toolkit\services\SettingsService;


class MessageCommand extends \app\bot\models\Command
{
    /** @var Contact */
    private $_contact;


    public function run(): void
    {
        if ($this->getMessage()->getContact() != null) {
            $this->enterPhone();
        } else {
            $this->{$this->getContact()->step}();
        }
    }


    public function enterName(): void
    {
        $text = trim($this->getMessage()->getText());
        $text = preg_replace('/[^a-zA-ZА-Яа-я0-9-\s$]/u', '', $text);

        if ((new TextValidator())->isValid($text)) {
            $sendPhoneButton = array_merge($this->getBot()->getOptions()->buttons['sendPhone'], [
                'request_contact' => true
            ]);

            $this->getBot()->setReplyKeyboardMarkup([[$sendPhoneButton]]);

            $this->getContact()->update([
                'name' => $text,
                'step' => ClaimBotConst::STEP_ENTER_PHONE,
            ]);

            $key = ClaimBotConst::STEP_ENTER_PHONE;
        } else {
            $key = $this->getContact()->step . '.error';
        }

        $this->getBot()->sendMessage($this->getUserId(), $key, null, [
            'userName' => $text,
            'contact' => $this->getContact(),
        ]);
    }


    public function enterPhone(): void
    {
        if ($this->getMessage()->getContact() != null) {
            $text = $this->getMessage()->getContact()->getPhoneNumber();
        } else {
            $text = trim($this->getMessage()->getText());
        }

        if ((new PhoneValidator())->isValid($text)) {
            $this->getContact()->update([
                'phone' => $text,
                'step' => ClaimBotConst::STEP_FINALE,
                'status' => ClaimBotConst::CONTACT_STATUS_FINALE,
            ]);

            $this->addButton();

            $key = ClaimBotConst::STEP_FINALE;
        } else {
            $key = $this->getContact()->step . '.error';
        }

        $this->getBot()->sendMessage($this->getUserId(), $key, null, [
            'phone' => $text,
            'contact' => $this->getContact(),
        ]);

        $googleSheet = new GoogleSheet(
            SettingsService::load('claim/google_sheet', GoogleSheetDto::class)
        );

        $data  = $this->getContact()->getAttributes(['id', 'name', 'phone']);
        $data[] = date('Y-m-d H:i:s');

        $googleSheet->save([$data]);
    }


    public function finale()
    {
        $this->addButton();

        $this->getBot()->sendMessage($this->getUserId(), ClaimBotConst::STEP_FINALE, null, [
            'contact' => $this->getContact(),
            'phone' => $this->getContact()->phone,
        ]);
    }


    private function addButton()
    {
        if (!empty($this->getBot()->getOptions()->buttons['finale'])) {
            $this->getBot()->setInlineKeyboardMarkup([
                [$this->getBot()->getOptions()->buttons['finale']]
            ]);
        }
    }


    private function getContact(): ?Contact
    {
        if ($this->_contact === null) {
            $this->_contact = Contact::repository()->findById($this->getUserId())->asEntityOne();
        }

        return $this->_contact;
    }
}