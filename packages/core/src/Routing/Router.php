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

        $properties = array_filter($parameters, fn($key) => $key[0] !== '_', ARRAY_FILTER_USE_KEY);

        $arguments = $this->getArguments($controller, $action, $properties);

        $controller->$action(...$arguments);
    }

    private function getArguments($controller, $action, $properties): array
    {
        $reflection = new \ReflectionMethod($controller, $action);

        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        $arguments = [];

        foreach ($properties as $name => $value) {
            if (!isset($parameters[$name])) {
                continue;
            }

            $parameter = $parameters[$name];
            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                $value = match ($type->getName()) {
                    'int' => (int) $value,
                    'float' => (float) $value,
                    'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
                    'string' => (string) $value,
                    default => $value,
                };
            }

            $arguments[$name] = $value;
        }

        return $arguments;
    }
}