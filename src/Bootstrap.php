<?php

namespace app\claimBot;

use app\claimBot\controllers\http\ClaimBotController;
use app\toolkit\components\bootstrap\BootstrapInterface;
use app\toolkit\components\Route;


class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Route::add([
            'claim-bot-handler' => ClaimBotController::class,
        ]);
    }
}
