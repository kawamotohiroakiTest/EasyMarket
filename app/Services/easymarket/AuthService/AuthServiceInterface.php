<?php

namespace App\Services\easymarket\AuthService;

use App\Services\easymarket\Dtos\OperationResult;
use App\Services\easymarket\AuthService\Dtos\AccessToken;

interface AuthServiceInterface
{
    public function signup(string $email, string $password): OperationResult;
}