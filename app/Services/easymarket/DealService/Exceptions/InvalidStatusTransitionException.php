<?php
namespace App\Services\easymarket\DealService\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct()
    {
        parent::__construct('取引のステータス遷移が不正です。');
    }
}