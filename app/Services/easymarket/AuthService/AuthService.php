<?php

namespace App\Services\easymarket\AuthService;

use App\Models\User;
use App\Notifications\easymarket\SignupVerify;
use App\Services\easymarket\Dtos\OperationResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{

    const API_TOKEN_NAME = 'easymarketApiAccessToken';

    /**
     * ユーザー仮登録処理
     *
     * @param  string  $email
     * @param  string  $password
     * @return OperationResult
     */
    public function signup(string $email, string $password): OperationResult
    {
        $user = (new User([
            'email' => $email,
            'password' => Hash::make($password),
        ]));

        if (!$user->save()) {
            return new OperationResult(false);
        }

        // 仮登録メール送信
        // Illuminate\Auth\Events\Registered eventは使わない。本登録用URLをSPA側URLにする独自実装をしたいという都合のため。
        $verificationUrl = $this->createVerificationUrl($user);
        $user->notify(new SignupVerify($verificationUrl));

        return new OperationResult(true);
    }
    
    /**
     * ユーザー本登録用URLの作成
     *
     * @param  \App\Models\User $user
     * @return string
     */
    private function createVerificationUrl(User $user): string
    {
        $frontendAppVerifyPageUrl = config('app.frontend_url') . '/signup/verify';
        $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60));
        $signature = hash_hmac(
            'sha256',
            $user->getKey() . $expires->getTimestamp(),
            config('app.key')
        );

        $query = http_build_query([
            'id' => $user->getKey(),
            'expires' => $expires->getTimestamp(),
            'signature' => $signature
        ]);

        return $frontendAppVerifyPageUrl . '?' . $query;
    }
}
