<?php

namespace app\claimBot\commands;

use app\bot\models\Message;
use app\claimBot\constants\ClaimBotConst;
use app\claimBot\entities\Contact;


class StartCommand extends \app\bot\models\Command
{
    public function run(): void
    {
        Contact::repository()->delete(['id' => $this->getUserId()]);

        Contact::repository()->create([
            'id' => $this->getUserId(),
            'step' => ClaimBotConst::STEP_ENTER_NAME,
            'status' => ClaimBotConst::CONTACT_STATUS_NEW
        ]);

        $message = new Message($this->getBot()->getOptions());
        $message->setMessageView(ClaimBotConst::STEP_ENTER_NAME);

        $userName = $this->getBot()->getIncomeMessage()->getSenderFullName();

        if ($userName) {
            $message
                ->setAttributes(['userName' => $userName])
                ->setReplyKeyboardMarkup([[['text' => $userName]]]);
        }

        $this->getBot()->sendMessage($message);
    }
}