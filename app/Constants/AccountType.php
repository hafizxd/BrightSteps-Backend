<?php

namespace App\Constants;

use App\Constants\Constant;

class AccountType extends Constant
{
    const PARENT = 1;
    const CHILD = 2;

    public static function labels()
    {
        return [
            static::PARENT => 'parent',
            static::CHILD => 'child',
        ];
    }
}
