<?php

/**
 * This file contains Tests\BHR\Router\Exceptions\RouteHandlerNotFoundExceptionTest
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

namespace Tests\BHR\Router\Exceptions;

use BHR\Router\Exceptions\RouteHandlerNotFoundException;
use Exception;
use PHPUnit\Framework\TestCase;

class RouteHandlerNotFoundExceptionTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(RouteHandlerNotFoundException::class));
    }

    public function testConstructor(): void
    {
        $message = 'hello';
        $code = 12;
        $previous = new Exception('route not found');
        $path = '/user/profile';

        $exception = new RouteHandlerNotFoundException($message, $code, $previous, $path);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($previous, $exception->getPrevious());
        $this->assertEquals($path, $exception->getPath());
    }
}
