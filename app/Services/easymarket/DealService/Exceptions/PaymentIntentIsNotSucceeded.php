<?php
namespace App\Services\easymarket\DealService\Exceptions;

use Exception;

class PaymentIntentIsNotSucceededException extends Exception
{
    public function __construct()
    {
        parent::__construct('決済処理が完了していません。');
    }
}