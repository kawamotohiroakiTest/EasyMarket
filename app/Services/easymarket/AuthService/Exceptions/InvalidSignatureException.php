<?php
namespace App\Services\easymarket\AuthService\Exceptions;

use Exception;

class InvalidSignatureException extends Exception
{
    public function __construct()
    {
        parent::__construct('認証情報が不正です。');
    }
}