# Security Best Practices for BHR Router

## Introduction

The BHR Router is designed with a clear separation of concerns. The router's responsibility is limited to:
- Matching URL patterns to routes
- Extracting parameters from URLs
- Dispatching requests to the appropriate handler

Security validation of parameter **values** is intentionally NOT the router's responsibility. This document explains why and provides best practices for implementing security in your application.

## Separation of Responsibilities

### What the Router Validates

The router validates the **structure** of routes:
- ✅ Route pattern syntax is valid
- ✅ Parameter names are non-empty
- ✅ URL structure matches the defined pattern

### What the Router Does NOT Validate

The router does not validate the **content** of parameters:
- ❌ Path traversal sequences (../, ..\)
- ❌ SQL injection attempts
- ❌ XSS payloads
- ❌ File type restrictions
- ❌ Numeric ranges or formats

## Why Validation Belongs in Handlers

### 1. Context-Dependent Requirements

Different parameters require different validation based on how they're used:

```php
// File download - NEEDS path traversal protection
$app->get('/download/{filename}', function($request) {
    $filename = $request->getParameter('filename');

    // The handler knows this will access the filesystem
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        throw new SecurityException('Invalid filename');
    }

    return readfile('/safe/uploads/' . $filename);
});

// Database search - path traversal is irrelevant
$app->get('/search/{query}', function($request) {
    $query = $request->getParameter('query');

    // "../" in a search query is harmless
    // But SQL escaping IS important
    $stmt = $db->prepare('SELECT * FROM products WHERE name LIKE ?');
    $stmt->execute(['%' . $query . '%']);

    return $stmt->fetchAll();
});

// Navigation breadcrumb - might WANT to allow "../"
$app->get('/navigate/{path}', function($request) {
    $path = $request->getParameter('path');

    // Application might intentionally support relative navigation
    return generateBreadcrumb($path);
});
```

### 2. Domain-Specific Logic

Only the handler knows the business requirements:

```php
// User ID must be numeric
$app->get('/user/{id}', function($request) {
    $id = $request->getParameter('id');

    if (!ctype_digit($id)) {
        throw new ValidationException('User ID must be numeric');
    }

    return User::find($id);
});

// Slug can contain letters, numbers, and hyphens
$app->get('/article/{slug}', function($request) {
    $slug = $request->getParameter('slug');

    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        throw new ValidationException('Invalid article slug');
    }

    return Article::findBySlug($slug);
});
```

### 3. Industry Standard Practice

Major routing libraries follow this same principle:
- **Symfony Router**: Parameters are passed as-is to controllers
- **Laravel Router**: Validation happens in controllers or form requests
- **Express.js**: Middleware and route handlers validate parameters
- **FastRoute**: No parameter validation in the router

## Security Best Practices

### 1. File System Access

When parameters will be used to access files, always validate for path traversal:

```php
$app->get('/file/{path}', function($request) {
    $path = $request->getParameter('path');

    // Method 1: Reject any directory traversal
    if (str_contains($path, '..') || str_contains($path, '/') || str_contains($path, '\\')) {
        throw new SecurityException('Invalid file path');
    }

    // Method 2: Use realpath to resolve and validate
    $basePath = '/var/www/uploads';
    $fullPath = realpath($basePath . '/' . $path);

    if ($fullPath === false || !str_starts_with($fullPath, $basePath)) {
        throw new SecurityException('Invalid file path');
    }

    return readfile($fullPath);
});
```

### 2. Database Queries

Always use prepared statements, regardless of parameter source:

```php
$app->get('/product/{category}', function($request) use ($db) {
    $category = $request->getParameter('category');

    // NEVER do this:
    // $result = $db->query("SELECT * FROM products WHERE category = '$category'");

    // ALWAYS use prepared statements:
    $stmt = $db->prepare('SELECT * FROM products WHERE category = ?');
    $stmt->execute([$category]);

    return $stmt->fetchAll();
});
```

### 3. HTML Output

Escape parameters before outputting to HTML:

```php
$app->get('/profile/{username}', function($request) {
    $username = $request->getParameter('username');

    // Escape for HTML output
    $safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    return "<h1>Profile: $safeUsername</h1>";
});
```

### 4. Command Execution

Never pass parameters directly to system commands:

```php
$app->get('/convert/{filename}', function($request) {
    $filename = $request->getParameter('filename');

    // Validate strictly
    if (!preg_match('/^[a-zA-Z0-9_-]+\.(jpg|png)$/', $filename)) {
        throw new ValidationException('Invalid filename');
    }

    // Use escapeshellarg for extra safety
    $safeFilename = escapeshellarg($filename);

    exec("convert /uploads/$safeFilename /processed/$safeFilename");
});
```

## Validation Middleware

For common validations, consider using middleware:

```php
// Create validation middleware
class FileParameterValidator implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $route = $request->getAttribute('route');
        $params = $route->getParameters();

        if (isset($params['filename'])) {
            if (str_contains($params['filename'], '..') || str_contains($params['filename'], '/')) {
                throw new SecurityException('Invalid filename parameter');
            }
        }

        return $handler->handle($request);
    }
}

// Apply to specific routes
$app->add(new FileParameterValidator());
$app->get('/download/{filename}', $downloadHandler);
$app->get('/preview/{filename}', $previewHandler);
```

## Security Checklist

When implementing route handlers, ask yourself:

- [ ] **File System**: Will this parameter be used to access files? → Add path traversal protection
- [ ] **Database**: Will this parameter be used in queries? → Use prepared statements
- [ ] **HTML Output**: Will this parameter be displayed? → Escape for HTML
- [ ] **System Commands**: Will this parameter be used in commands? → Validate strictly and escape
- [ ] **Numeric IDs**: Should this parameter be numeric? → Validate type and range
- [ ] **Format Requirements**: Does this parameter have a specific format? → Use regex validation
- [ ] **Length Limits**: Should this parameter have length restrictions? → Check string length
- [ ] **Character Sets**: Should this parameter only contain certain characters? → Validate character set

## Example: Comprehensive Handler Validation

Here's an example of a well-validated route handler:

```php
$app->get('/api/v1/files/{userId}/{filename}', function($request) {
    $userId = $request->getParameter('userId');
    $filename = $request->getParameter('filename');

    // Validate user ID
    if (!ctype_digit($userId) || $userId < 1 || $userId > 999999) {
        throw new ValidationException('Invalid user ID');
    }

    // Validate filename
    if (strlen($filename) > 255) {
        throw new ValidationException('Filename too long');
    }

    if (!preg_match('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+$/', $filename)) {
        throw new ValidationException('Invalid filename format');
    }

    // Prevent path traversal
    if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
        throw new SecurityException('Path traversal attempt detected');
    }

    // Check file extension
    $allowedExtensions = ['jpg', 'png', 'pdf', 'doc', 'docx'];
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowedExtensions)) {
        throw new ValidationException('File type not allowed');
    }

    // Build safe path
    $basePath = '/var/www/user_files';
    $userPath = $basePath . '/' . $userId;
    $filePath = $userPath . '/' . $filename;

    // Verify final path is within bounds
    $realPath = realpath($filePath);
    if ($realPath === false || !str_starts_with($realPath, $userPath)) {
        throw new SecurityException('Invalid file path');
    }

    // Check file exists
    if (!file_exists($realPath)) {
        throw new NotFoundException('File not found');
    }

    // Return file
    return new FileResponse($realPath);
});
```

## Summary

The BHR Router follows the principle of separation of concerns. It extracts parameters but doesn't validate their contents because:

1. **It can't know** how parameters will be used
2. **It shouldn't assume** what validation is needed
3. **It would limit flexibility** if it enforced validation rules

Security validation is the responsibility of route handlers and middleware, where the context and requirements are known. This design provides maximum flexibility while maintaining security when properly implemented at the application level.

Remember: **The router extracts, the handler validates.**