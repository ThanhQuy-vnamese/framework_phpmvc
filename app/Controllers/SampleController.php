<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller\BaseController;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SampleController extends BaseController
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index (): string
    {
        return $this->twig->render('base');
    }
    public function login (): string
    {
        return $this->twig->render('pages/login');
    }
    public function register (): string
    {
        return $this->twig->render('pages/register');
    }
    public function statistics (): string
    {
        return $this->twig->render('pages/statistics');
    }


}
