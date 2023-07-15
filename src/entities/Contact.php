<?php

namespace app\claimBot\entities;

use app\toolkit\components\Entity;
use app\toolkit\components\repository\Repository;
use app\claimBot\repository\ContactsRepository;


class Contact extends Entity
{
    public $id;
    public $name;
    public $step;
    public $phone;
    public $status;



    public static function fields(): array
    {
        return [
            'integer' => ['id', 'status'],
            'string' => ['name', 'step', 'phone'],
        ];
    }


    public static function repository(): Repository
    {
        return new ContactsRepository(self::class);
    }
}