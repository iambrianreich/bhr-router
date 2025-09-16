<?php

/**
 * This file contains BHR\Router\RequestHandlers\CallableRequestHandler.
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

namespace BHR\Router\RequestHandlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableRequestHandler implements RequestHandlerInterface
{
    /**
     * The callable to be invoked when handling a request.
     *
     * @todo if PHP ever supports setting callable as the explicit type for a property, do that.
     * @var callable
     */
    protected $callable;

    /**
     * Creates a new CallableRequestHandler instance.
     *
     * @param callable $callable The callable to be invoked when handling a request.
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Handles a request and produces a response by invoking the stored callable.
     *
     * @param ServerRequestInterface $request The server request to handle.
     * @return ResponseInterface The response produced by the callable.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func($this->callable, $request);
    }
}