<?php

declare(strict_types=1);

namespace BHR\Router\HandlerLocators;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface IHandlerLocator {
    public function locate(RequestInterface $request): RequestHandlerInterface;
}