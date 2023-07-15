<?php

namespace app\common\bots\vacancy\repository;

use app\common\components\repository\Repository;


class ContactsRepository extends Repository
{
    public static function tableName(): string
    {
        return 'vacancy_contact';
    }
}