<?php

namespace App\Services\easymarket\UserService;

use App\Models\User;

interface UserServiceInterface
{
    public function update(User $user, array $params): User;
}