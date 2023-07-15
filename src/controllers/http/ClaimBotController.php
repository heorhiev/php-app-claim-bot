<?php

namespace app\claimBot\controllers\http;

use app\claimBot\ClaimBot;
use app\toolkit\services\LoggerService;


class ClaimBotController implements \app\toolkit\components\controllers\HttpControllerInterface
{
    public function main()
    {
        try {
            (new ClaimBot())->handler();
        } catch (\Exception $e) {
            LoggerService::error($e);
        }
    }
}