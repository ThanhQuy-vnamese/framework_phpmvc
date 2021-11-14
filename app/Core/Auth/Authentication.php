<?php

declare(strict_types=1);

namespace App\Core\Auth;


use App\Core\Cookie\Cookie;
use App\Core\Session;
use App\Model\User;

class Authentication
{
    /**
     * @var User|mixed|string|null
     */
    private $user;

    /**
     * @var User|mixed|string|null
     */
    private $result;
    // private Session $session;
    // private Cookie $cookie;
    // private bool $rememberPassword;

    public function __construct()
    {
        $this->session = new Session();
        $this->cookie = new Cookie();
        $this->user = $this->session->get('user') ?? null;
    }

    public function login(array $info, bool $rememberPassword = false): bool
    {
        $this->rememberPassword = $rememberPassword;
        $where = $info;
        if (array_key_exists('password', $info)) {
            unset($where['password']);
        }
        $user = new User();
        $result = $user->getOne($where);

        return $this->validate($result, $info['password']);
    }

    private function validate($result, $password): bool
    {
        if (!$result) {
            return false;
        }

        $info = password_get_info($password);
        $isExistCost = isset($info['options']['cost']);

        if (!$isExistCost) {
            if (password_verify($password, $result->password)) {
                return $this->setInfo($result);
            }

            return false;
        }

        if ($info['options']['cost'] !== 10) {
            return false;
        }

        return $this->setInfo($result);
    }

    private function setInfo($result): bool
    {
        $this->result = $result;
        $this->session->set('user', $result);
        if ($this->rememberPassword) {
            $this->cookie->set('user', $result->email);
        }
        return true;
    }

    public function user()
    {
        return $this->user;
    }

    public function setRememberPassword($isRememberPassword) {
        $this->rememberPassword = $isRememberPassword;
    }

    public function setUserInfo(array $info) {
        $this->setInfo((object) $info);
    }

    public function getUser()
    {
        return $this->result;
    }

    public function isLogin(): bool
    {
        $session = new Session();
        if ($session->get('user') && !empty($this->session->get('user'))) {
            return true;
        }
        return false;
    }

    public function logout()
    {
        $this->session->remove('user');
        if ($this->cookie->get('user')) {
            $this->cookie->remove('user');
        }
    }
}