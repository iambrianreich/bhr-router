<?php

/**
 * This file contains BHR\Router\HTTP\Verb.
 *
 * @author Brian Reich <brian@brianreich.dev>
 * @copyright Copyright (C) 2025 Brian Reich
 * @since 2025/09/01
 */

declare(strict_types=1);

namespace BHR\Router\HTTP;

/**
 * Legal HTTP request verbs.
 */
enum Verb: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case HEAD = 'HEAD';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';
}
