<?php

declare(strict_types=1);

namespace App;

use App\Command\MigrationCommand;
use App\Config\Config;
use App\Controller\ProductController;
use App\Database\ConnectionFactory;
use App\Middleware\BearerTokenMiddleware;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use App\Service\TestProductFactory;
use App\Validation\ProductValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

class Application
{
    private ContainerBuilder $container;

    public function __construct()
    {
        $this->container = new ContainerBuilder();
        $this->setupContainer();
    }

    private function setupContainer(): void
    {
        $this->container->register(Config::class, Config::class);
        
        $this->container->register(ConnectionFactory::class, ConnectionFactory::class)
            ->addArgument(new Reference(Config::class));
            
        $this->container->register(\PDO::class, \PDO::class)
            ->setFactory([new Reference(ConnectionFactory::class), 'create'])
            ->setPublic(true);

        $this->container->register(MigrationCommand::class, MigrationCommand::class)
            ->addArgument(new Reference(\PDO::class));

        $this->container->register(ProductRepository::class, ProductRepository::class)
            ->addArgument(new Reference(\PDO::class));

        $this->container->register(ProductValidator::class, ProductValidator::class)
            ->addArgument(new Reference(ProductRepository::class));

        $this->container->register(ProductService::class, ProductService::class)
            ->addArgument(new Reference(ProductRepository::class));

        $this->container->register(\Symfony\Contracts\HttpClient\HttpClientInterface::class, \Symfony\Contracts\HttpClient\HttpClientInterface::class)
            ->setFactory([HttpClient::class, 'create']);

        $this->container->register(TestProductFactory::class, TestProductFactory::class)
            ->addArgument(new Reference(ProductRepository::class))
            ->addArgument(new Reference(\Symfony\Contracts\HttpClient\HttpClientInterface::class));

        $this->container->register(ProductController::class, ProductController::class)
            ->addArgument(new Reference(ProductService::class))
            ->addArgument(new Reference(ProductValidator::class))
            ->addArgument(new Reference(TestProductFactory::class));

        $this->container->register(BearerTokenMiddleware::class, BearerTokenMiddleware::class)
            ->addArgument(new Reference(Config::class));
    }

    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }

    public function handle(Request $request): Response
    {
        $routes = new RouteCollection();
        
        $routes->add('health', new Route('/health', ['_controller' => [ProductController::class, 'health']]));
        $routes->add('product_create', new Route('/products', ['_controller' => [ProductController::class, 'create']], [], [], '', [], ['POST']));
        $routes->add('product_update', new Route('/products/{id}', ['_controller' => [ProductController::class, 'update']], ['id' => '\d+'], [], '', [], ['PUT']));
        $routes->add('product_delete', new Route('/products/{id}', ['_controller' => [ProductController::class, 'delete']], ['id' => '\d+'], [], '', [], ['DELETE']));
        $routes->add('product_list', new Route('/products', ['_controller' => [ProductController::class, 'list']], [], [], '', [], ['GET']));
        $routes->add('product_test', new Route('/products/test', ['_controller' => [ProductController::class, 'createTest']], [], [], '', [], ['POST']));

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($routes, $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());
            $controllerAction = $parameters['_controller'];
            $controller = $this->container->get($controllerAction[0]);
            $action = $controllerAction[1];

            $execution = function (Request $request) use ($controller, $action, $parameters) {
                unset($parameters['_route'], $parameters['_controller']);
                return $controller->$action($request, ...array_values($parameters));
            };

            // Apply middleware for all /products routes
            if (str_starts_with($request->getPathInfo(), '/products')) {
                /** @var BearerTokenMiddleware $middleware */
                $middleware = $this->container->get(BearerTokenMiddleware::class);
                return $middleware->handle($request, $execution);
            }

            return $execution($request);

        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException) {
            return new Response('Not Found', 404);
        } catch (\Symfony\Component\Routing\Exception\MethodNotAllowedException) {
            return new Response('Method Not Allowed', 405);
        } catch (Throwable $e) {
            return new Response('Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    public function runMigrations(): void
    {
        $this->container->get(MigrationCommand::class)->migrate();
    }
}
