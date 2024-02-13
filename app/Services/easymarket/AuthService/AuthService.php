<?php

namespace App\Services\easymarket\AuthService;

use App\Models\User;
use App\Notifications\easymarket\SignupVerify;
use App\Services\easymarket\Dtos\OperationResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Verified;
use App\Services\easymarket\AuthService\Exceptions\InvalidSignatureException;
use App\Services\easymarket\AuthService\Exceptions\UserAlreadyVerifiedException;
use App\Services\easymarket\AuthService\Exceptions\UserNotFoundException;
use App\Services\easymarket\AuthService\Dtos\AccessToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use App\Services\easymarket\AuthService\Exceptions\InvalidCredentialsException;



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

    /**
     * 認証情報が正しいか検証する
     *
     * @param  int  $id
     * @param  int  $expires
     * @param  string  $signature
     * @return bool
     */
    private function verifySignature(int $id, int $expires, string $signature): bool
    {
        $calculatedSignature = hash_hmac(
            'sha256',
            $id . $expires,
            config('app.key')
        );

        return $calculatedSignature === $signature;
    }

    /**
     * メール認証をして、認証情報が正しければユーザー本登録する
     *
     * @param  int  $id
     * @param  int  $expires
     * @param  string  $signature
     * @exception InvalidSignatureException
     * @exception UserAlreadyVerifiedException
     * @exception UserNotFoundException
     * @return AccessToken
     */
    public function signupVerify(int $id, int $expires, string $signature): AccessToken
    {
        if (!$this->verifySignature($id, $expires, $signature)) {
            throw new InvalidSignatureException();
        }

        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if ($user->hasVerifiedEmail()) {
            throw new UserAlreadyVerifiedException();
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        $accessToken = $user->createToken(self::API_TOKEN_NAME)->plainTextToken;

        return new AccessToken($accessToken, $user);
    }

    /**
     * ログインする＝ログイン情報が正しければアクセストークンを返す
     *
     * @param  string  $email
     * @param  string  $password
     * @exception AuthenticationException
     * @exception InvalidCredentialsException
     * @return AccessToken
     */
    public function signin(string $email, string $password): AccessToken
    {
        $user = User::where('email', $email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            throw new AuthenticationException('メールアドレス認証が完了していません。');
        }

        if (!$user || !Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException();
        }
    
        $plainTextToken = $user->createToken(self::API_TOKEN_NAME)->plainTextToken;
    
        return new AccessToken($plainTextToken, $user);
    }

    /**
     * ログアウトする＝ユーザーの発行済みアクセストークンを削除する
     * 
     * @return OperationResult
     */
    public function signout(): OperationResult
    {
        if (!Auth::check()) {
            return new OperationResult(false);
        }

        /** @var User $user **/
        $user = Auth::user();

        $user->tokens()->delete();
        return new OperationResult(true);
    }

}
