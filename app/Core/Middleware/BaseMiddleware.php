<?php
namespace App\Core\Middleware;

abstract class BaseMiddleware
{
    abstract public function __invoke(): void;
}
