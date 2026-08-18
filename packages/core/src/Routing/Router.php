<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router as SymfonyRouter;

readonly class Router
{
    public function __construct(
        private Container     $container,
        private SymfonyRouter $router,
    ) {
    }

    public function match(): void
    {
        $request = Request::createFromGlobals();
        $parameters = $this->router->matchRequest($request);

        [$controllerClass, $action] = explode('::', $parameters['_controller']);
        $controller = $this->container->get($controllerClass);
        $controller->$action($parameters);
    }
}