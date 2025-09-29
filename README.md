# bhr/router

A simple PHP router for simple PHP projects

## Features

- `BHR\Router\Application` - Front controller where routes and route groups are registered.


## Installation

Install into your project:

```bash
composer require bhr/router
```

### Verify Installation

After installation, verify the router is working by creating a test file:

```php
// test-router.php
<?php
require 'vendor/autoload.php';

use BHR\Router\Application;
use BHR\Router\HandlerLocators\DefaultHandlerLocator;

// If this runs without errors, the router is installed correctly
$app = new Application(new DefaultHandlerLocator());
echo "✓ BHR Router installed successfully!\n";
```

Run the test:
```bash
php test-router.php
```

## Usage/Examples

### Basic Routing

```php
<?php
require 'vendor/autoload.php';

use BHR\Router\Application;
use BHR\Router\HandlerLocators\DefaultHandlerLocator;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

// Create the application
$app = new Application(new DefaultHandlerLocator());

// Define routes
$app->get('/hello', function($request) {
    return new Response(200, [], 'Hello, World!');
});

// Route with parameters
$app->get('/user/{id}', function($request) {
    // The router passes parameters through the route
    $route = $request->getAttribute('route');
    $userId = $route->getParameters()['id'];

    return new Response(200, [], "User ID: $userId");
});

// Handle multiple HTTP methods
$app->post('/users', function($request) {
    return new Response(201, [], 'User created');
});

$app->put('/users/{id}', function($request) {
    return new Response(200, [], 'User updated');
});

$app->delete('/users/{id}', function($request) {
    return new Response(204);
});

// Handle the request
$request = ServerRequest::fromGlobals();
try {
    $response = $app->handle($request);
} catch (\BHR\Router\Exceptions\RouteHandlerNotFoundException $e) {
    $response = new Response(404, [], 'Not Found');
}

// Send the response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getBody();
```

### Using Middleware

```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware implements MiddlewareInterface {
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check authentication
        $token = $request->getHeaderLine('Authorization');

        if (!$this->isValidToken($token)) {
            return new Response(401, [], 'Unauthorized');
        }

        // Pass to next handler
        return $handler->handle($request);
    }

    private function isValidToken(string $token): bool {
        // Your authentication logic here
        return $token === 'Bearer valid-token';
    }
}

// Add middleware to the application
$app->add(new AuthMiddleware());
```

### Route Parameters

```php
// Multiple parameters in a route
$app->get('/posts/{year}/{month}/{slug}', function($request) {
    $route = $request->getAttribute('route');
    $params = $route->getParameters();

    $year = $params['year'];
    $month = $params['month'];
    $slug = $params['slug'];

    return new Response(200, [], "Post: $year/$month/$slug");
});

// Parameters can appear anywhere in the route
$app->get('/{lang}/products/{category}', function($request) {
    $route = $request->getAttribute('route');
    $params = $route->getParameters();

    $language = $params['lang'];
    $category = $params['category'];

    return new Response(200, [], "Language: $language, Category: $category");
});
```

## Running Tests

To run tests, run the following command

```bash
composer test
```


## License

[MIT](https://choosealicense.com/licenses/mit/)

## Authors

- [@therealbrianreich](https://www.github.com/therealbrianreich)
