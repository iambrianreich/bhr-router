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
enum Verb
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case PATCH;
    case HEAD;
    case TRACE;
    case CONNECT;
}
