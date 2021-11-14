<?php

declare(strict_types=1);

namespace App\Adapter;


use App\Adapter\Object\UserObject;
use App\Model\User;

class UserAdapter extends BaseAdapter
{
    private array $user;

    /**
     * @var UserObject[]
     */
    private array $userObject;

    public function getUsers(): UserAdapter
    {
        $user = new User();
        $userModel = $user->getUsers();
        $this->set($userModel);

        $userObject = [];
        foreach ($this->user as $result) {
            $userObject[] = new UserObject($result);
        }

        $this->userObject = $userObject;

        return $this;
    }

    public function set($data)
    {
        $this->user = $data;
    }

    /**
     * @return UserObject[]
     */
    public function get(): array
    {
        return $this->userObject;
    }

    public function toArray(): array
    {
        return $this->user;
    }
}