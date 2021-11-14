<?php
declare(strict_types=1);

namespace App\Core\View;

use App\Core\Helper\Helper;
use App\Core\Session;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;

class Twig {
    public Environment $twig;

    public function __construct($pathToView, array $options = [])
    {
        $loader = new FilesystemLoader($pathToView);
        $twig = new Environment($loader, $options);
        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addGlobal('helper', new Helper());
        $twig->addGlobal('session', Session::class);
        $this->twig = $twig;
    }

    public function addGlobalFunction($name, $value) {
        $this->twig->addGlobal($name, $value);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function render(string $templateName, array $params = []): string
    {
        $template = $templateName . '.twig';
        return $this->twig->render($template, $params);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function load(string $templateName): TemplateWrapper
    {
        $template = $templateName . '.twig';
        return $this->twig->load($template);
    }
}
