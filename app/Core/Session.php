<?php


namespace App\Core;


class Session
{
    const FLASH_MESSAGE = 'flash_message';

    public function __construct()
    {
        $this->startSession();
        $flashMessages = $_SESSION[self::FLASH_MESSAGE] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            $flashMessage['removed'] = true;
        }
        $_SESSION[self::FLASH_MESSAGE] = $flashMessages;
    }

    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function setFlash(string $key, $value)
    {
        $_SESSION[self::FLASH_MESSAGE][$key] = [
            'removed' => false,
            'value'   => $value
        ];
    }

    public function getFlash(string $key)
    {
        $flash = $_SESSION[self::FLASH_MESSAGE][$key]['value'] ?? false;
        $this->destroyFlash();
        return $flash;
    }

    public function hasFlash(string $key)
    {
        return $_SESSION[self::FLASH_MESSAGE][$key]['value'] ?? false;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return $_SESSION[$key] ?? '';
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function destroyFlash()
    {
        $flashMessages = $_SESSION[self::FLASH_MESSAGE] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            if ($flashMessage['removed']) {
                unset($flashMessages[$key]);
            }
        }
        $_SESSION[self::FLASH_MESSAGE] = $flashMessages;
    }
}
