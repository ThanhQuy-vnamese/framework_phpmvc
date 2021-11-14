<?php

declare(strict_types=1);

namespace App\Core\Cookie;


use App\Core\Exception\CookieNotExistException;

class Cookie
{
    public function set(string $name, string $value, int $timeExpire = 2592000): Cookie
    {
        setcookie($name, $value, time() + $timeExpire);
        return $this;
    }

    /**
     * @throws CookieNotExistException
     */
    public function get(string $name, bool $throwError = false)
    {
        $cookie = (isset($_COOKIE[$name]) && !empty($_COOKIE[$name])) ? $_COOKIE[$name] : false;
        if ($throwError) {
            throw new CookieNotExistException("Can not get ${name} from cookie.", 404);
        }

        return $cookie;
    }

    public function remove(string $name): bool
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            setcookie($name, null, -1, '/');
            return true;
        } else {
            return false;
        }
    }
}