<?php
namespace App\Services\easymarket\AuthService\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('ユーザーが見つかりません。');
    }
}