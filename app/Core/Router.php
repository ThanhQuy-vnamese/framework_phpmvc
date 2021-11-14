<?php

namespace App\Core;

use App\Core\Controller\BaseController;
use App\Core\Request\Request;
use App\Core\Response\Response;

class Router
{
    protected array $router = [];
    protected Request $request;
    protected Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param $path
     * @param $callback
     * @param string $middleware
     * @return $this
     */
    public function get($path, $callback, string $middleware = ''): Router
    {
        $this->router['get'][$path] = $callback;
        if (!empty($middleware)) {
            $this->router['get'][$path]['middleware'] = $middleware;
        }
        return $this;
    }

    /**
     * @param $path
     * @param $callback
     * @param string $middleware
     * @return $this
     */
    public function post($path, $callback, string $middleware = ''): Router
    {
        $this->router['post'][$path] = $callback;
        if (!empty($middleware)) {
            $this->router['get'][$path]['middleware'] = $middleware;
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function resolve()
    {
        $path = $this->request->getPath();
        $pos = strpos($path, '/public');
        if ($pos !== false) {
            $path = substr($path, $pos + strlen('/public'));
        }
        $method = $this->request->getMethod();
        // TODO: Refactor
        $class = $this->router[$method][$path] ?? false;
        $callback = $class;
        unset($callback['middleware']);
        if ($callback === false) {
            $this->response->setStatusCode(404);

            return Application::$APPLICATION->twig->render('_404');
        }
        if (is_array($callback)) {
            /**@var BaseController $controller */
            $controller = new $callback[0](Application::$APPLICATION->twig, $this->request, $this->response);
            Application::$APPLICATION->controller = $controller;
            $controller->action = $callback[1];
            $callback[0] = $controller;

            if (!empty($class['middleware'])) {
                $middleware = [];
                $middleware[0] = new $class['middleware']();
                $middleware[1] = '__invoke';
                call_user_func($middleware);
            }
        }
        return call_user_func($callback);
    }
}
