<?php
namespace App\Core\Middleware;
use App\Core\Auth\Authentication;
use App\Core\Middleware\BaseMiddleware;
use App\Core\Response\Response;

class AuthMiddleware extends BaseMiddleware{

    public function __invoke(): void
    {
        $authentication = new Authentication();
        $response = new Response();
        if (!$authentication->isLogin()) {
            $response->redirect('/login');
        }
    }
}
