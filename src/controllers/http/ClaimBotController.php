<?php

namespace app\claimBot\controllers\http;

use app\claimBot\ClaimBot;


class ClaimBotController implements \app\toolkit\components\controllers\HttpControllerInterface
{
    public function main()
    {
        (new ClaimBot('claim/telegram'))->run();
    }
}