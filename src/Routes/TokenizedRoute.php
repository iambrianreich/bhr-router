<?php

/**
 * This file contains BHR\$CLASS
*/

namespace BHR\Router\Routes;

use BadMethodCallException;
use BHR\Router\IParameterizedRoute;
use BHR\Router\IRoute;
use InvalidArgumentException;

/**
 * A TokenizedRoute is a route which may specify named tokens as part of the
 * route. When the route specifies named tokens, the values of the tokens from
 * the most recent call to match() can be retrieved with getArguments().
 *
 * Example:
 *
 * $route = new TokenizedRoute('user/{id}');
 * $route->matches('user/12'); // Returns true
 * $route->getArguments(); // Returns ['id' => 12]
 */
class TokenizedRoute implements IParameterizedRoute
{
    public const PATH_SEPARATOR = '/';
    public const ERROR_INVALID_TOKEN = '"%" is an invalid url token';

    /**
     * A hash of the named arguments and their values fmro the most recent
     * call to matches().
     */
    protected ?array $arguments;

    /**
     * Creates a new TokenizedRoute.
     *
     * @param string[] $tokens The list of tokens for the route.
     * @throws InvalidArgumentException if any array items are not strings.
     */
    public function __construct(protected array $tokens)
    {
        foreach ($tokens as $token) {
            if (! is_string($token)) {
                throw new InvalidArgumentException(sprintf(self::ERROR_INVALID_TOKEN, $token));
            }
        }

        $this->arguments = null;
    }

    /**
     * Creates a TokenizedRoute from a string by splitting it on the path
     * separator ("/").
     *
     * Example:
     * $route = TokenizedRoute::fromPath('user/{id}/metadata');
     * // Equivalent to new TokenizedRoute(['user'], '{id}', 'metadata'])
     *
     * @param string $path The path to convert to a TokenizedRoute.
     * @return self
     */
    public static function fromPath(string $path): self
    {
        return new TokenizedRoute(explode(self::PATH_SEPARATOR, $path));
    }

    /**
     * Returns true if the specified path matches the TokenizedRoute.
     *
     * The path matches the route if:
     *
     * 1. They have the same number of tokens.
     * 2. String tokens are identical.
     *
     * Examples:
     *
     * $route = TokenizedRoute::fromString('user/{id}/groups');
     * $route->matches('user/12/groups'); // Returns true
     *
     * $route = TokenizedRoute::fromString('group/{id}/metadata');
     * $route->matches('group/12'); // Returns false
     *
     * @param string $path The path to test
     * @return bool Returns true if the patch matches the TokenizedRoute
     */
    public function matches(string $path): bool
    {
        $pathTokens = explode('/', $path);
        $this->arguments = null;
        $arguments = [];

        if (count($pathTokens) !== count($this->tokens)) {
            return false;
        }

        for ($index = 0; $index < count($pathTokens); $index++) {
            $matches = [];
            if (preg_match('/^{(.*)}$/', $this->tokens[$index], $matches)) {
                $arguments[$matches[1]] = $pathTokens[$index];
                continue;
            }

            if ($this->tokens[$index] !== $pathTokens[$index]) {
                return false;
            }
        }

        $this->arguments = $arguments;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getParameters(): array
    {
        if ($this->arguments === null) {
            throw new BadMethodCallException('matches() not called or last call failed');
        }

        return $this->arguments;
    }
}
