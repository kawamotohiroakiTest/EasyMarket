<?php

namespace App\Http\Controllers\easymarket\API;

use App\Exceptions\APIBusinessLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\easymarket\API\Auth\SignupRequest;
use App\Http\Requests\easymarket\API\Auth\SignupVerifyRequest;
use App\Http\Requests\easymarket\API\Auth\SigninRequest;
use App\Http\Requests\easymarket\API\Auth\SignoutRequest;
use App\Http\Resources\easymarket\API\AccessTokenResource;
use App\Http\Resources\easymarket\API\OperationResultResource;
use App\Services\easymarket\AuthService\Exceptions\InvalidSignatureException;
use App\Services\easymarket\AuthService\AuthServiceInterface;
use App\Services\easymarket\AuthService\Exceptions\UserAlreadyVerifiedException;
use App\Services\easymarket\AuthService\Exceptions\InvalidCredentialsException;

class AuthController extends Controller
{

    /**
    * @var AuthServiceInterface
    */
    private $authService;

    /**
     * @param  AuthServiceInterface $authService
     * @return void
     */
    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 会員仮登録API
     * 
     * @param  SignupRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(SignupRequest $request)
    {
        $params = $request->safe()->toArray();
        $email = $params['email'];
        $password = $params['password'];
        $operationResult = $this->authService->signup($email, $password);

        return (new OperationResultResource($operationResult))->response()->setStatusCode(201);
    }

    /**
     * 会員本登録API
     *
     * @param  SignupVerifyRequest $request
     * @return AccessTokenResource
     */
    public function signupVerify(SignupVerifyRequest $request)
    {
        $params = $request->safe()->toArray();
        $userId = $params['id'];
        $expires = $params['expires'];
        $signature = $params['signature'];

        try {
            $accessToken = $this->authService->signupVerify($userId, $expires, $signature);
        } catch (InvalidSignatureException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        } catch (UserAlreadyVerifiedException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }

        return new AccessTokenResource($accessToken);
    }

    /**
     * ログインAPI
     * 
     * @param  SigninRequest $request
     * @return AccessTokenResource
     */
    public function signin(SigninRequest $request)
    {
        $params = $request->safe()->toArray();
        $email = $params['email'];
        $password = $params['password'];

        try {
            $accessToken = $this->authService->signin($email, $password);
        } catch (InvalidCredentialsException $e) {
            throw new APIBusinessLogicException($e->getMessage(), 400);
        }

        return new AccessTokenResource($accessToken);
    }

    /**
     * ログアウトAPI
     * 
     * @param  SignoutRequest $request
     * @return OperationResultResource
     */
    public function signout(SignoutRequest $request)
    {
        $operationResult = $this->authService->signout();

        return new OperationResultResource($operationResult);
    }
}


