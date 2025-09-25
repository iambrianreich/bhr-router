<?php

/**
 * This file contains Tests\BHR\Router\HandlerLocators\DefaultHandlerLocatorTest
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

namespace Test\BHR\Router\HandlerLocators;

use BHR\Router\Exceptions\RouteHandlerNotFoundException;
use BHR\Router\HandlerLocators\DefaultHandlerLocator;
use BHR\Router\HTTP\Verb;
use BHR\Router\IRoute;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WeakMap;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class DefaultHandlerLocatorTest extends TestCase
{
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DefaultHandlerLocator::class));
    }

    public function testConstructor(): void {
        $locator = new DefaultHandlerLocator();
        $this->assertInstanceOf(DefaultHandlerLocator::class, $locator);
    }

    public function testAddRouteSupportsCallable(): void {
        $locator = new DefaultHandlerLocator();

        $verb = Verb::GET;
        $route = $this->createMock(IRoute::class);
        $handler = function (RequestInterface $request): RequestHandlerInterface {
            // Dummy handler
            return new class implements RequestHandlerInterface {
                public function handle(RequestInterface $request): \Psr\Http\Message\ResponseInterface {
                    // Dummy response
                    throw new Exception("Not implemented");
                }
            };
        };

        $result = $locator->addRoute($verb, $route, $handler);
        $this->assertInstanceOf(DefaultHandlerLocator::class, $result);
    }

    public function testAddRouteSupportsRequestHandlerInterface(): void {
        $locator = new DefaultHandlerLocator();

        $verb = Verb::POST;
        $route = $this->createMock(IRoute::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $result = $locator->addRoute($verb, $route, $handler);
        $this->assertInstanceOf(DefaultHandlerLocator::class, $result);
    }

    public function testLocateThrowsInvalidArgumentExceptionForUnsupportedVerb(): void {
        $this->expectException(InvalidArgumentException::class);

        $locator = new DefaultHandlerLocator();
        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('INVALID_VERB');

        $locator->locate($request);
    }

    public function testLocateThrowsRouteHandlerNotFoundExceptionForUnregisteredRoute(): void {
        $this->expectException(RouteHandlerNotFoundException::class);
        $this->expectExceptionMessage('Handler not found for /unregistered');
        
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/unregistered');

        $locator = new DefaultHandlerLocator();
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $locator->locate($request);
    }

    public function testLocateThrowsRouteHandlerNotFoundExceptionWhenRoutesAreRegisteredButDoNotMatch(): void {
        $this->expectException(RouteHandlerNotFoundException::class);
        $this->expectExceptionMessage('Handler not found for /no-match');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/no-match');

        $route = $this->createMock(IRoute::class);
        $route->method('matches')->willReturn(false);

        $locator = new DefaultHandlerLocator();
        $locator->addRoute(Verb::GET, $route, function (RequestInterface $request): RequestHandlerInterface {
            return new class implements RequestHandlerInterface {
                public function handle(RequestInterface $request): \Psr\Http\Message\ResponseInterface {
                    throw new Exception("Not implemented");
                }
            };
        });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $locator->locate($request);
    }

    public function testLocateReturnsMatchingRoute(): void {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/match');

        $route = $this->createMock(IRoute::class);
        $route->method('matches')->willReturn(true);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $locator = new DefaultHandlerLocator();
        $locator->addRoute(Verb::GET, $route, $handler);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $result = $locator->locate($request);
        $this->assertSame($handler, $result);
    }
}