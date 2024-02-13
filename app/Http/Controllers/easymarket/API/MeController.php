<?php

namespace App\Http\Controllers\easymarket\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\easymarket\API\Me\ShowRequest;
use App\Http\Requests\easymarket\API\Me\UpdateRequest;
use App\Http\Resources\easymarket\API\MeResource;
use App\Services\easymarket\ProductService\ProductServiceInterface;
use App\Services\easymarket\UserService\UserServiceInterface;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{

    /**
    * @var UserServiceInterface
    */
    private $userService;

    /**
    * @var ProductServiceInterface
    */
    private $productService;

    /**
     * @param  UserServiceInterface  $userService
     * @param  ProductServiceInterface  $productService
     * @return void
     */
    public function __construct(
        UserServiceInterface $userService,
    )
    {
        $this->userService = $userService;
    }

    /**
     * ログインユーザー情報取得API
     * 
     * @param  ShowRequest  $request
     * @return MeResource
     */
    public function show(ShowRequest $request)
    {
        $user = Auth::user();

        return new MeResource($user);
    }

    /**
     * ログインユーザー情報更新API
     * 
     * @param  UpdateRequest  $request
     * @return MeResource
     */
    public function update(UpdateRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $params = $request->safe()->toArray();

        $user = $this->userService->update($user, $params);

        return new MeResource($user);
    }


}
