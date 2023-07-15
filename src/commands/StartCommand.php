<?php

namespace app\claimBot\commands;

use app\claimBot\constants\ClaimBotConst;
use app\claimBot\entities\Contact;


class StartCommand extends \app\bot\Command
{
    public function run(): void
    {
        $chat = $this->getMessage()->getChat();

        Contact::repository()->delete(['id' => $this->getUserId()]);

        Contact::repository()->create([
            'id' => $this->getUserId(),
            'step' => ClaimBotConst::STEP_ENTER_NAME,
            'status' => ClaimBotConst::CONTACT_STATUS_NEW
        ]);

        $userName = trim($chat->getFirstName() . ' ' . $chat->getLastName());

        if ($userName) {
            $this->getBot()->setReplyKeyboardMarkup(
                [[['text' => $userName]]]
            );
        }

        $this->getBot()->sendMessage($this->getUserId(), ClaimBotConst::STEP_ENTER_NAME, null, [
            'userName' => $userName,
        ]);
    }
}