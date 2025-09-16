<?php

/**
 * This file contains BHR\Router\HandlerLocators\DefaultHandlerLocator
 *
 * Copyright $YEAR$$ Brian Reich
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the “Software”), to deal in the
 * Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author Brian Reich <brian@brianreich.dev>
 * @copyright Copyright (C) $YEAR$$ Brian Reich
 * @license MIT
 */

declare(strict_types=1);

namespace BHR\Router\HandlerLocators;

use BHR\Router\HTTP\Verb;
use BHR\Router\IRoute;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DefaultHandlerLocator implements IHandlerLocator
{
    /**
     * @var array<Verb, array<IRoute, IRequestHandlerInterface>>
     */
    protected array $routes = [];

    public function __construct()
    {
    }

    public function addRoute(Verb $verb, IRoute $route, callable $handler): self
    {
        if (! isset($this->routes[$verb])) {
            $this->routes[$verb] = [];
        }

        $this->routes[$verb][] = [$route, $handler];
        return $this;
    }

    public function locate(RequestInterface $request): RequestHandlerInterface
    {
        $verb = Verb::tryFrom($request->getMethod());

        // If request method is not a verb we recognize, then there is no match.
        if ($verb === null) {
            throw new InvalidArgumentException('Invalid HTTP verb ' . $request->getMethod());
        }

        $path = $request->getUri()->getPath();

        foreach ($this->routes[$verb] as $routeDefinition) {
            $route = $routeDefinition[0];
            $handler = $routeDefinition[1];

            if ($route->matches($path)) {
                return $handler;
            }
        }

        throw new Exception('Handler not found for ' . $path);
    }
}
