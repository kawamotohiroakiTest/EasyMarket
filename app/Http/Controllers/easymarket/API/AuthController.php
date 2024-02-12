<?php

namespace App\Http\Controllers\easymarket\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\easymarket\API\Auth\SignupRequest;
use App\Http\Resources\easymarket\API\OperationResultResource;
use App\Services\easymarket\AuthService\AuthServiceInterface;

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

}
