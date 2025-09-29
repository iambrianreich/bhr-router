<?php

/**
 * This file contains Tests\BHR\Router\Routes\TokenizedRouteTest
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

namespace Tests\BHR\Router\Routes;

use BHR\Router\Routes\TokenizedRoute;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TokenizedRouteTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(TokenizedRoute::class));
    }

    public function testMatchesFailedIfDifferentNumberOfTokens(): void
    {
        $route = TokenizedRoute::fromPath('user/{userId}');
        $path = 'user/profile/12';
        $this->assertFalse($route->matches($path));
    }

    public function testReturnsTrueForExactMatch(): void
    {
        $route = TokenizedRoute::fromPath('user/profile');
        $this->assertTrue($route->matches('user/profile'));
    }

    public function testReturnsTrueForArgumentMatch(): void
    {
        $route = TokenizedRoute::fromPath('user/{id}');
        $this->assertTrue($route->matches('user/12'));
        $this->assertEquals('12', $route->getParameters()['id']);
    }

    // Validation Tests

    public function testFromPathThrowsExceptionForEmptyParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty parameter names are not allowed');
        TokenizedRoute::fromPath('user/{}');
    }

    public function testFromPathThrowsExceptionForInvalidParameterName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name "123invalid"');
        TokenizedRoute::fromPath('user/{123invalid}');
    }

    public function testFromPathThrowsExceptionForParameterNameStartingWithNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name "1id"');
        TokenizedRoute::fromPath('user/{1id}');
    }

    public function testFromPathThrowsExceptionForParameterNameWithInvalidCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name "user-id"');
        TokenizedRoute::fromPath('api/{user-id}');
    }

    public function testFromPathThrowsExceptionForParameterNameWithSpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name "user id"');
        TokenizedRoute::fromPath('api/{user id}');
    }

    public function testFromPathThrowsExceptionForDuplicateParameters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate parameter name "id"');
        TokenizedRoute::fromPath('user/{id}/profile/{id}');
    }

    public function testFromPathThrowsExceptionForPathTooLong(): void
    {
        $longPath = str_repeat('a', TokenizedRoute::MAX_PATH_LENGTH + 1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route path exceeds maximum length');
        TokenizedRoute::fromPath($longPath);
    }

    public function testFromPathThrowsExceptionForTooManySegments(): void
    {
        $segments = array_fill(0, TokenizedRoute::MAX_SEGMENTS + 1, 'segment');
        $longPath = implode('/', $segments);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route path exceeds maximum of');
        TokenizedRoute::fromPath($longPath);
    }

    public function testFromPathThrowsExceptionForSegmentTooLong(): void
    {
        $longSegment = str_repeat('a', TokenizedRoute::MAX_SEGMENT_LENGTH + 1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route segment');
        $this->expectExceptionMessage('exceeds maximum length');
        TokenizedRoute::fromPath("user/$longSegment");
    }

    // Valid parameter names

    public function testFromPathAcceptsValidParameterNames(): void
    {
        // These should all work without throwing exceptions
        $validRoutes = [
            'user/{id}',
            'api/{userId}',
            'post/{_id}',
            'article/{post_id}',
            'category/{_category_name}',
            'files/{fileName123}',
            'search/{QUERY}',
        ];

        foreach ($validRoutes as $routePath) {
            $route = TokenizedRoute::fromPath($routePath);
            $this->assertInstanceOf(TokenizedRoute::class, $route);
        }
    }

    public function testFromPathAcceptsComplexValidRoutes(): void
    {
        $complexRoutes = [
            'api/v1/users/{userId}/posts/{postId}/comments/{commentId}',
            'admin/{_section}/manage/{entity_type}',
            'files/{directory}/{subdirectory}/{filename}',
            '{lang}/category/{cat_id}/product/{product_slug}',
        ];

        foreach ($complexRoutes as $routePath) {
            $route = TokenizedRoute::fromPath($routePath);
            $this->assertInstanceOf(TokenizedRoute::class, $route);
        }
    }

    // Edge cases

    public function testFromPathAcceptsEmptyPath(): void
    {
        $route = TokenizedRoute::fromPath('');
        $this->assertInstanceOf(TokenizedRoute::class, $route);
    }

    public function testFromPathAcceptsRootPath(): void
    {
        $route = TokenizedRoute::fromPath('/');
        $this->assertInstanceOf(TokenizedRoute::class, $route);
    }

    public function testMatchesThrowsExceptionForEmptyParameterInRuntime(): void
    {
        // Create route with direct constructor (bypassing fromPath validation)
        $route = new TokenizedRoute(['user', '{}']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty parameter names are not allowed');
        $route->matches('user/123');
    }

    // Boundary testing

    public function testFromPathAcceptsMaximumPathLength(): void
    {
        // Create a path that's at maximum length but with valid segments
        $segmentLength = 200; // Less than MAX_SEGMENT_LENGTH (255)
        $segmentsNeeded = intval(TokenizedRoute::MAX_PATH_LENGTH / ($segmentLength + 1)); // +1 for separator
        $segments = array_fill(0, $segmentsNeeded, str_repeat('a', $segmentLength));
        $maxPath = implode('/', $segments);

        // Ensure we're at or under the maximum path length
        $this->assertLessThanOrEqual(TokenizedRoute::MAX_PATH_LENGTH, strlen($maxPath));

        $route = TokenizedRoute::fromPath($maxPath);
        $this->assertInstanceOf(TokenizedRoute::class, $route);
    }

    public function testFromPathAcceptsMaximumSegments(): void
    {
        $segments = array_fill(0, TokenizedRoute::MAX_SEGMENTS, 'seg');
        $maxSegmentsPath = implode('/', $segments);
        $route = TokenizedRoute::fromPath($maxSegmentsPath);
        $this->assertInstanceOf(TokenizedRoute::class, $route);
    }

    public function testFromPathAcceptsMaximumSegmentLength(): void
    {
        $maxSegment = str_repeat('a', TokenizedRoute::MAX_SEGMENT_LENGTH);
        $route = TokenizedRoute::fromPath("user/$maxSegment");
        $this->assertInstanceOf(TokenizedRoute::class, $route);
    }
}
