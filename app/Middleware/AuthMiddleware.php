<?php


namespace App\Middleware;


use App\Core\Auth\Authentication;
use App\Core\Exception\ForbiddenException;
use App\Core\Middleware\BaseMiddleware;
use App\Core\Response\Response;

class AuthMiddleware extends BaseMiddleware
{
    public function __invoke(): void
    {
        $authentication = new Authentication();
        if (!$authentication->isLogin()) {
//            throw new ForbiddenException();
            $response = new Response();
            $response->redirect('/login');
        }
    }
}
