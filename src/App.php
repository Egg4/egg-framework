<?php

namespace Egg;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\CallableResolver;
use Slim\Collection;
use FastRoute\Dispatcher;
use RuntimeException;
use SplStack;
use SplDoublyLinkedList;

class App extends \Slim\App
{
    public function __construct($container = [])
    {
        $container['settings'] =new Collection([
            'httpVersion' => '1.1',
            'responseChunkSize' => 4096,
            'outputBuffering' => 'append',
            'determineRouteBeforeAppMiddleware' => true,
            'displayErrorDetails' => false,
            'addContentLengthHeader' => true,
            'routerCacheFile' => false,
        ]);

        if (!isset($container['components'])) {
            $container['components'] = [];
        }

        $container['callableResolver'] = function ($container) {
            return new CallableResolver($container);
        };

        parent::__construct($container);
    }

    public function run($silent = false)
    {
        $container = $this->getContainer();
        $componentCollection = new \Egg\Component\Collection();
        foreach($container['components'] as $component) {
            $componentCollection->set(get_class($component), $component);
            $component->setContainer($container);
            $component->init();
        }

        $stack = array_reverse($componentCollection->stack());
        foreach($stack as $component) {
            $this->add($component);
        }

        return parent::run($silent);
    }

    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        // Ensure basePath is set
        $router = $this->getContainer()->get('router');
        if (is_callable([$request->getUri(), 'getBasePath']) && is_callable([$router, 'setBasePath'])) {
            $router->setBasePath($request->getUri()->getBasePath());
        }

        $response = $this->callMiddlewareStack($request, $response);

        $response = $this->finalize($response);

        return $response;
    }

    protected function seedMiddlewareStack(callable $kernel = null)
    {
        if (!is_null($this->stack)) {
            throw new RuntimeException('MiddlewareStack can only be seeded once.');
        }
        if ($kernel === null) {
            $kernel = new \Egg\Component\Closure(function($request, $response) {
                return $response;
            });
        }
        $this->stack = new SplStack;
        $this->stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
        $this->stack[] = $kernel;
    }
}
