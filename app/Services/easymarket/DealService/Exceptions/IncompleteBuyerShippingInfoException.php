<?php
namespace App\Services\easymarket\DealService\Exceptions;

use Exception;

class IncompleteBuyerShippingInfoException extends Exception
{
    public function __construct()
    {
        parent::__construct('購入者情報が不足しています。購入するためには、購入者情報を入力してください。');
    }
}