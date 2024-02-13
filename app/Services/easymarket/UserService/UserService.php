<?php

namespace App\Services\easymarket\UserService;

use App\Models\User;
use App\Services\easymarket\ImageService\ImageServiceInterface;

class UserService implements UserServiceInterface
{

    /**
    * @var ImageServiceInterface
    */
    private $imageService;

    /**
     * @param  ImageServiceInterface  $imageService
     * @return void
     */
    public function __construct(
        ImageServiceInterface $imageService
        
    )
    {
        $this->imageService = $imageService;
    }

    /*
     * ユーザー更新
     * 
     * @param User $user
     * @param array $params
     * @return User
     */
    public function update(User $user, array $params): User
    {
        $user->fill($params);

        if (isset($params['profile_image'])) {
            $image = $this->imageService->saveUploadFile($params['profile_image']);
            $user->profileImage()->associate($image);
        }

        $user->save();

        return $user;
    }
}