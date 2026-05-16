---
title: Access Control
description: Manage who can view your documentation
order: 2
---

# Access Control

OI Laravel Documentation provides flexible access control through Laravel's middleware system. Manage who can view your documentation during installation or by modifying your configuration.

## Setting Up Access Control

### During Installation

When you run `php artisan doc:install`, the wizard prompts you to choose your access control preference:

```
How should documentation access be controlled?

  [1] Public - Anyone can view documentation
  [2] Authenticated - Only logged-in users can view
  [3] Custom - Specify custom middleware
```

Select an option (default is 1):
```

Based on your choice, the wizard automatically sets the appropriate middleware in your configuration.

### Manual Configuration

You can also configure access control after installation by editing `config/oi-documentation.php`:

```php
'route' => [
    'enabled' => true,
    'prefix' => 'documentation',
    'middleware' => ['web'], // Modify this array
],
```

## Access Control Scenarios

### Scenario 1: Public Documentation

Allow anyone to view your documentation without authentication.

```php
'route' => [
    'middleware' => ['web'],
],
```

**Use Cases:**
- Public-facing API documentation
- User guides for all visitors
- Help and FAQ sections
- Open-source project documentation

**Route Behavior:**
- Available at `/documentation` without login
- No authentication required
- Accessible to search engines

### Scenario 2: Authenticated Documentation

Restrict documentation to logged-in users only.

```php
'route' => [
    'middleware' => ['web', 'auth'],
],
```

**Use Cases:**
- Internal team documentation
- Premium member guides
- Authenticated user features documentation
- Private API documentation

**Route Behavior:**
- Redirects unauthenticated users to login
- Available at `/documentation` only after authentication
- Search engines cannot access (protected by auth)
- User context available in components

### Scenario 3: Role-Based Access

Restrict documentation to specific user roles or permissions.

```php
'route' => [
    'middleware' => ['web', 'auth', 'role:admin'],
],
```

**Use Cases:**
- Admin-only documentation
- Developer guides (staff-only)
- Advanced features (premium users)
- Internal documentation

**Custom Middleware Example:**

If you need custom permission logic, create a middleware:

```bash
php artisan make:middleware CheckDocumentationAccess
```

```php
// app/Http/Middleware/CheckDocumentationAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDocumentationAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Allow if user is authenticated and is a staff member
        if (auth()->check() && auth()->user()->isStaff()) {
            return $next($request);
        }

        abort(403, 'Documentation is not available.');
    }
}
```

Register it in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'check-docs' => CheckDocumentationAccess::class,
    ]);
})
```

Then use it in configuration:

```php
'route' => [
    'middleware' => ['web', 'auth', 'check-docs'],
],
```

### Scenario 4: Version-Based Access

Provide different documentation based on user tier or API version:

```php
'route' => [
    'middleware' => ['web', 'auth', 'check-api-version'],
],
```

### Scenario 5: IP-Restricted Access

Restrict to specific IP addresses (for internal networks):

```php
'route' => [
    'middleware' => ['web', 'throttle:60,1', 'ip-whitelist'],
],
```

## Changing Access Control After Installation

### Update Middleware Array

Modify the middleware array in `config/oi-documentation.php`:

```php
// Before: Public documentation
'middleware' => ['web'],

// After: Authenticated only
'middleware' => ['web', 'auth'],
```

### Add Rate Limiting

Prevent abuse by adding Laravel's `throttle` middleware:

```php
'middleware' => ['web', 'throttle:100,1'], // 100 requests per minute
```

### Combine Multiple Middleware

Stack multiple middleware for layered protection:

```php
'middleware' => [
    'web',                  // Session handling
    'auth',                 // Authentication
    'verified',             // Email verification
    'check-subscription',   // Custom middleware
],
```

## Testing Access Control

### Testing with PHPUnit/Pest

Test your access control with browser tests:

```php
it('allows public access to documentation', function () {
    $response = $this->get('/documentation');
    $response->assertSuccessful();
});

it('requires authentication for protected documentation', function () {
    config(['oi-documentation.route.middleware' => ['web', 'auth']]);
    
    $response = $this->get('/documentation');
    $response->assertRedirectToRoute('login');
    
    $response = $this->actingAs(User::factory()->create())
        ->get('/documentation');
    $response->assertSuccessful();
});
```

### Browser Testing

With Pest's browser testing capabilities:

```php
it('redirects unauthenticated users from protected docs', function () {
    config(['oi-documentation.route.middleware' => ['web', 'auth']]);
    
    $page = visit('/documentation');
    $page->assertSee('Log in');
});

it('shows documentation to authenticated users', function () {
    config(['oi-documentation.route.middleware' => ['web', 'auth']]);
    
    $page = $this->actingAs(User::factory()->create())
        ->visit('/documentation');
    $page->assertSee('Documentation');
});
```

## Best Practices

1. **Match your documentation audience** - Choose access control that aligns with your users
2. **Test after changes** - Always test access control modifications
3. **Document your choice** - Add comments explaining why you chose a specific middleware stack
4. **Use environment variables** - For different environments:
   ```php
   'middleware' => explode(',', env('DOCUMENTATION_MIDDLEWARE', 'web')),
   ```
5. **Monitor access** - Log documentation access for security audits
6. **Keep middleware list short** - Avoid excessive middleware for better performance

## Troubleshooting

### "This action is unauthorized"

Your middleware is denying access. Check:
- User authentication status
- User role or permission
- Custom middleware conditions
- IP whitelist configuration

### Inconsistent Access

Ensure all deployment environments have matching middleware configuration. Environment variables should be consistent across staging and production.

### Search Engine Access

If you want search engines to index public documentation, keep middleware simple (`['web']` only). If you add `auth`, robots cannot crawl the pages.
