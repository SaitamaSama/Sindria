<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 7/11/16
 * Time: 10:05 PM
 */

namespace Sindria;
class TemplateLoader {
    private $twig;

    public function __construct() {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/Application/Pages/');
        $this->twig = new \Twig_Environment($loader);
    }

    public function load(string $templateName, array $vars = []): string {
        return $this->
        twig->
        loadTemplate($templateName)
            ->render($vars);
    }
}