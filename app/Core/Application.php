<?php

namespace App\Core;

use App\Core\Controller\BaseController;
use App\Core\Database\Database;
use App\Core\Database\DBModel;
use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\View\Twig;
use App\Model\User;

class Application
{
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'beforeAfter';

    protected array $eventListeners = [];

    public string $layout = 'main';
    public User $userClass;
    public static string $ROOT_DIR;
    public static Application $APPLICATION;
    public Database $database;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public Twig $twig;
    public ?DBModel $user;
    public ?BaseController $controller = null;

    public function __construct(string $rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$APPLICATION = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response);
        $this->database = new Database($config['db']);
    }

    public function setTwigTemplate(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return BaseController
     */
    public function get_controller(): BaseController
    {
        return $this->controller;
    }

    /**
     * @param BaseController $controller
     */
    public function set_controller(BaseController $controller): void
    {
        $this->controller = $controller;
    }

    public function run()
    {
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);
//        try {
            echo $this->router->resolve();
//        } catch (\Exception $e) {
//            echo $this->twig->render(
//                '_error'
//            );
//        }
    }

    public function triggerEvent(string $eventName)
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    public function on($eventName, $callback)
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }

    public function login(DBModel $user): bool
    {
        $this->user = $user;
        var_dump($user);
        die;
        $primaryKey = $this->getPrimaryKey();
//        $primaryValue = $this->{$primaryKey};
//        var_dump($primaryKey);die;
        $this->session->set('user', $user);
        return true;
    }
}
