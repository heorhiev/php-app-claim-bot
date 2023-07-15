<?php

namespace app\claimBot\repository;

use app\toolkit\components\repository\Repository;


class ContactsRepository extends Repository
{
    public static function tableName(): string
    {
        return 'vacancy_contact';
    }
}