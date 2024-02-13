<?php
namespace App\Services\easymarket\ProductService\Exceptions;

use Exception;

class IncompleteSellerInfoException extends Exception
{
    public function __construct()
    {
        parent::__construct('出品者情報が不足しています。');
    }
}