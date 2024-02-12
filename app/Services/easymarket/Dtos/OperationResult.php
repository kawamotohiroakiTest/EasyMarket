<?php

namespace App\Services\easymarket\Dtos;

class OperationResult
{
    public bool $success;

    public function __construct(bool $success)
    {
        $this->success = $success;
    }
    
}