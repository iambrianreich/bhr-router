<?php
/**
 * This file contains Tests\BHR\Router\ApplicationTest
 *
 * Copyright 2025 Brian Reich
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
 * @copyright Copyright (C) 2025 Brian Reich
 * @license MIT
 */

declare(strict_types=1);

namespace Tests\BHR\Router;

use BHR\Router\Application;
use BHR\Router\HandlerLocators\DefaultHandlerLocator;
use BHR\Router\HandlerLocators\IHandlerLocator;
use BHR\Router\HTTP\Verb;
use BHR\Router\IRoute;
use BHR\Router\Routes\TokenizedRoute;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase {
    public function testConstuctorWithNoArgumentsUsesDefaultHandlerLocator(): void {
        $application = new Application();
        $locator = $application->getHandlerLocator();
        $this->assertInstanceOf(DefaultHandlerLocator::class, $locator);
    }

    public function testConstructorAcceptsAnyIHandlerLocator(): void {
        $locator = $this->createMock(IHandlerLocator::class);
        $application = new Application($locator);
        $this->assertSame($locator, $application->getHandlerLocator());
    }

    public function testAddRouteCallsHandlerLocatorAddRoute(): void {
        $route = $this->createMock(IRoute::class);
        $handler = fn () => true;
        $routeHandler = $this->createMock(IHandlerLocator::class);
        $routeHandler
            ->expects($this->once())
            ->method('addRoute');
        $application = new Application($routeHandler);
        $application->addRoute(Verb::GET, $route, $handler);
    }

    public function testAddRouteAutomaticallyConvertsStringRoutes(): void {
        $route = '/user/{id}';
        $handler = fn () => true;
        $routeHandler = $this->createMock(IHandlerLocator::class);
        $routeHandler
            ->expects($this->once())
            ->method('addRoute')
            ->with(
                Verb::POST,
                TokenizedRoute::fromPath($route),
                $handler
            );

        $application = new Application($routeHandler);
        $application->addRoute(Verb::POST, $route, $handler);
    }

    public function testGet(): void {
        $verb = Verb::GET;
        $route = '/users/{id}';
        $callable = fn () => true;
        $locator = $this->createMock(IHandlerLocator::class);
        $locator
            ->expects($this->once())
            ->method('addRoute')
            ->with(
                $verb,
                TokenizedRoute::fromPath($route),
                $callable
            );
        
        $application = new Application($locator);
        $application->get($route, $callable);
    }

    public function testPost(): void {
        $verb = Verb::POST;
        $route = '/users/{id}';
        $callable = fn () => true;
        $locator = $this->createMock(IHandlerLocator::class);
        $locator
            ->expects($this->once())
            ->method('addRoute')
            ->with(
                $verb,
                TokenizedRoute::fromPath($route),
                $callable
            );
        
        $application = new Application($locator);
        $application->post($route, $callable);
    }

    public function testPatch(): void {
        $verb = Verb::PATCH;
        $route = '/users/{id}';
        $callable = fn () => true;
        $locator = $this->createMock(IHandlerLocator::class);
        $locator
            ->expects($this->once())
            ->method('addRoute')
            ->with(
                $verb,
                TokenizedRoute::fromPath($route),
                $callable
            );
        
        $application = new Application($locator);
        $application->patch($route, $callable);
    }

    public function testPut(): void {
        $verb = Verb::PUT;
        $route = '/users/{id}';
        $callable = fn () => true;
        $locator = $this->createMock(IHandlerLocator::class);
        $locator
            ->expects($this->once())
            ->method('addRoute')
            ->with(
                $verb,
                TokenizedRoute::fromPath($route),
                $callable
            );
        
        $application = new Application($locator);
        $application->put($route, $callable);
    }

    public function testDelete(): void {
        $verb = Verb::DELETE;
        $route = '/users/{id}';
        $callable = fn () => true;
        $locator = $this->createMock(IHandlerLocator::class);
        $locator
            ->expects($this->once())
            ->method('addRoute')
            ->with(
                $verb,
                TokenizedRoute::fromPath($route),
                $callable
            );
        
        $application = new Application($locator);
        $application->delete($route, $callable);
    }
}

