<?php

declare(strict_types=1);

namespace MaxServ\Core;

use Exception;
use MaxServ\Core\Routing\Router;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

readonly class Bootstrap
{
    /**
     * @return void
     * @throws Exception
     */
    public function boot(): void
    {
        $container = new ContainerBuilder();
        $this->configure($container);

        $loader = new YamlFileLoader($container, new FileLocator(APPLICATION_ROOT . '/packages'));

        $finder = new Finder();
        $serviceFiles = $finder->files()->in(APPLICATION_ROOT . '/packages')->name('services.yaml');

        foreach ($serviceFiles as $serviceFile) {
            $loader->load($serviceFile->getPathname());
        }

        $container->compile();

        /** @var Router $router */
        $router = $container->get(Router::class);
        $router->match();
    }

    private function configure(ContainerBuilder $container): void
    {
        $container->setParameter('application.root', APPLICATION_ROOT);

        $container->setParameter('db.host', getenv('DB_HOST'));
        $container->setParameter('db.user', getenv('DB_USER'));
        $container->setParameter('db.password', getenv('DB_PASSWORD'));
        $container->setParameter('db.database', getenv('DB_DATABASE'));
    }
}
