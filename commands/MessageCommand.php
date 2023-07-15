<?php

namespace app\common\bots\vacancy\commands;

use app\common\bots\vacancy\constants\VacancyBotConst;
use app\common\components\validators\TextValidator;
use app\common\components\validators\PhoneValidator;
use app\common\dto\config\GoogleSheetDto;
use app\common\services\googleSheets\UploadService;
use app\common\services\SettingsService;


class MessageCommand extends Command
{
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
                'step' => VacancyBotConst::STEP_ENTER_PHONE,
            ]);

            $key = VacancyBotConst::STEP_ENTER_PHONE;
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
                'step' => VacancyBotConst::STEP_FINALE,
                'status' => VacancyBotConst::CONTACT_STATUS_FINALE,
            ]);

            $this->addButton();

            $key = VacancyBotConst::STEP_FINALE;
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

        $this->getBot()->sendMessage($this->getUserId(), VacancyBotConst::STEP_FINALE, null, [
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
}