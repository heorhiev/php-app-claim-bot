<?php

namespace app\claimBot\commands;

use app\claimBot\constants\ClaimBotConst;
use app\claimBot\entities\Contact;
use app\toolkit\components\validators\TextValidator;
use app\toolkit\components\validators\PhoneValidator;
use app\toolkit\services\SettingsService;
use app\googleSheet\config\GoogleSheetDto;
use app\googleSheet\googleSheets\UploadService;



class MessageCommand extends \app\bot\Command
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

            $this->getBot()->setReplyKeyboardMarkup(
                [[['text' => 'Отправить номер', 'request_contact' => true]]]
            );

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

        $service = new UploadService(SettingsService::load('vacancy/google_sheet', GoogleSheetDto::class));

        $service->save([
            $this->getContact()->getAttributes(['id', 'name', 'phone'])
        ]);
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
        if ($this->getBot()->getOptions()->vacancyBotFinaleUrl) {
            $text = $this->getBot()->getOptions()->vacancyBotFinaleText;
            $url = $this->getBot()->getOptions()->vacancyBotFinaleUrl;

            $this->getBot()->setInlineKeyboardMarkup([
                [['text' => $text, 'url' => $url]]
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