<?php

namespace App\Core\Controller;

use App\Core\Application;
use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\View\Twig;

class BaseController
{
    protected Request $request;
    protected Response $response;
    public string $layout = 'main';
    public string $action = '';
    public Twig $twig;

    public function __construct(Twig $twig, Request $request, Response $response)
    {
        $this->twig = $twig;
        $this->request = $request;
        $this->response = $response;
    }
}
