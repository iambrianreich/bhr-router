<?php

namespace BHR;

use BHR\Router\HandlerLocators\IHandlerLocator;
use BHR\Router\HTTP\Verb;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface
{
    protected array $routes = [];

    public function __construct(
        private IHandlerLocator $handlerLocator
    ) {
    }

    private function getHandlerLocator(): IHandlerLocator
    {
        return $this->handlerLocator;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $verb = Verb::tryFrom(strtoupper($request->getMethod()));

        if ($verb === null) {
            // TODO Not a valid request method.
            // At this point I haven't decided if this is a 404 or something else
            // because I need to look at the HTTP spec and decide what the right
            // thing to do is, in the face of a nonstandard method.
        }

        // TODO What happens when not found?
        return $this->getHandlerLocator()->locate($request)->handle($request);
    }

    public function get(string $route, callable $handler): self
    {
        return $this->addRoute(Verb::GET, $route, $handler);
    }

    public function post(string $route, callable $handler): self
    {
        return $this->addRoute(Verb::POST, $route, $handler);
    }

    public function patch(string $route, callable $handler): self
    {
        return $this->addRoute(Verb::PATCH, $route, $handler);
    }

    public function put(string $route, callable $handler): self
    {
        return $this->addRoute(Verb::PUT, $route, $handler);
    }

    public function delete(string $route, callable $handler): self
    {
        return $this->addRoute(Verb::DELETE, $route, $handler);
    }


    private function addRoute(Verb $verb, string $route, callable $handler): self
    {
        if (! isset($this->routes[$verb])) {
            $this->routes[$verb] = [];
        }

        $this->routes[$verb][$route] = $handler;
        return $this;
    }
}
