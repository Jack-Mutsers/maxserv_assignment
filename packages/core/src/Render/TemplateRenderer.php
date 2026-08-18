<?php

declare(strict_types=1);

namespace MaxServ\Core\Render;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class TemplateRenderer
{
    public function __construct(
        private Environment $environment
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function render(string $template, array $arguments): string
    {
        return $this->environment->render($template, $arguments);
    }
}