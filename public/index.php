<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\SampleController;
use App\Controllers\MedicanRecordController;
use App\Core\Application;
use App\Core\Middleware\AuthMiddleware;
use App\Core\View\Twig;
use App\Model\User;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = [
    'userClass' => User::class,
    'db' => [
        'host' => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
        'database' => $_ENV['DB_DATABASE'],
    ]
];

$pathViews = dirname(__DIR__) . '/views';
$pathCache = dirname(__DIR__) . '/cache';

$options = [
    'cache' => $pathCache,
    'debug' => $_ENV['APP_DEBUG'],
];

$twig = new Twig($pathViews, $options);

$app = new Application(dirname(__DIR__), $config);
$app->setTwigTemplate($twig);
$app->twig->addGlobalFunction('session', Application::$APPLICATION->session);

//$app->on(Application::EVENT_BEFORE_REQUEST, function() {
//    echo 'Before request';
//});

$app->router->get('/', [SampleController::class, 'index']);
$app->router->get('/login', [AuthController::class, 'login']);
$app->router->get('/validate', [AuthController::class, 'validate']);
$app->router->get('/logout', [AuthController::class, 'logout']);
$app->router->post('/login_res', [AuthController::class, 'login']);
$app->router->get('/register', [AuthController::class, 'register']);
$app->router->post('/new_register', [AuthController::class, 'newRegister']);
$app->router->get('/verify', [AuthController::class, 'verifyAccount']);
$app->router->get('/statistics', [SampleController::class, 'statistics']);
$app->router->post('/medican_insert', [MedicanRecordController::class, 'insertForm']);
$app->router->post('/medican_update', [MedicanRecordController::class, 'updateForm']);
$app->router->get('/download', [MedicanRecordController::class, 'downloadQR']);
$app->router->get('/services', [MedicanRecordController::class, 'newForm'], AuthMiddleware::class);
$app->router->get('/yourrecord', [MedicanRecordController::class, 'yourRecord']);
$app->router->get('/resetpassword', [AuthController::class, 'sendmail']);
$app->router->post('/sendresetpass', [AuthController::class, 'sendmail_reset']);
$app->router->post('/reset', [AuthController::class, 'resetPassword']);
$app->router->get('/resetpass', [AuthController::class, 'reset_password']);
$app->router->post('/contact_insert', [ContactController::class, 'insert_contact']);
$app->router->get('/profile', [AuthController::class, 'getProfile'], AuthMiddleware::class);
$app->router->get('/edit_profile', [AuthController::class, 'profile_edit_view'], AuthMiddleware::class);
$app->router->post('/edit', [AuthController::class, 'profile_edit']);

$app->run();
