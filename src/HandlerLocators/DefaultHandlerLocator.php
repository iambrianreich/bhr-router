<?php

declare(strict_types=1);

namespace BHR\Router\HandlerLocators;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DefaultHandlerLocator implements IHandlerLocator
{
    public function locate(RequestInterface $request): RequestHandlerInterface
    {
        throw new \Exception('not implemented');
    }
}
