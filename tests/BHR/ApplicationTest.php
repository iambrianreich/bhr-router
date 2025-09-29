<?php
/**
 * This file contains BHR\Router\ApplicationTest
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

namespace BHR\Router;

use BHR\Router\HandlerLocators\DefaultHandlerLocator;
use BHR\Router\HandlerLocators\IHandlerLocator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationTest extends TestCase {
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Application::class));
    }

    public function testConstructor(): void {
        $handlerLocator = $this->createMock(IHandlerLocator::class);
        $application = new Application($handlerLocator);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertSame($handlerLocator, $application->getHandlerLocator());
    }

    public function testhandlerRunsMiddlewareInOrder(): void {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function(
                    ServerRequestInterface $request,
                    RequestHandlerInterface $handler
                ) {
                        return $this->createMock(ResponseInterface::class);
                    }
                );

        $app = new Application(new DefaultHandlerLocator());
        $app->get('/users', fn () => $this->createMock(ResponseInterface::class));
        $app->add($middleware1);

        $request = $this->createMock(ServerRequestInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/users');
        $request->method('getUri')->willReturn($uri);
        $request->method('getMethod')->willReturn('GET');
        $app->handle($request);
    }
}
