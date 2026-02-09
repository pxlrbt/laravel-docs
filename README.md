# Laravel Docs

Render markdown documentation inside Laravel.

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require pxlrbt/laravel-docs
```

Publish the config and assets:

```bash
php artisan vendor:publish --tag=docs-config
php artisan vendor:publish --tag=docs-assets
```

## Usage

Place markdown files in the configured root directory (default: `docs/`). Each file needs YAML frontmatter:

```yaml
---
title: "Getting Started"
description: "An introduction to the app"
group: "Basics"
order: 0
---

Your markdown content here.
```

Pages are accessible at `/{url_prefix}/{slug}`, e.g. `/docs/getting-started`.

## Configuration

The config file (`config/docs.php`) supports multiple doc instances as a keyed array:

```php
return [
    'default' => [
        'root' => env('DOCS_ROOT', 'docs'),           // Markdown files directory
        'url_prefix' => env('DOCS_URL_PREFIX', 'docs'),// URL path prefix
        'layout' => null,                              // Blade layout component to wrap content
        'requires_auth' => false,                      // Require authentication
        'auth_middleware' => ['auth'],                  // Middleware when auth is required
        'can_access_docs' => null,                     // Closure for fine-grained access control
        'has_navigation' => true,                      // Show sidebar navigation
        'has_sidebar' => true,                         // Show table of contents
        'has_pagination' => true,                      // Show prev/next page links
    ],
];
```

### Multiple Instances

Define additional instances to serve separate doc sections with different configs:

```php
return [
    'default' => [
        'root' => 'docs',
        'url_prefix' => 'docs',
    ],
    'api' => [
        'root' => 'docs/api',
        'url_prefix' => 'api-docs',
        'requires_auth' => true,
    ],
];
```

### Access Control

Use the `can_access_docs` callback for fine-grained access control:

```php
'can_access_docs' => function ($user) {
    return $user?->isAdmin();
},
```

### Custom Layout

Wrap the docs in your app's layout by setting the `layout` option to a Blade component name:

```php
'layout' => 'layouts.app',
```

When no layout is set, docs render as a standalone page with their own styles.

## Features

- GitHub-flavored markdown (tables, strikethrough, etc.)
- Auto-generated table of contents from h2/h3 headings
- Heading permalink anchors
- Sidebar navigation grouped by frontmatter `group`
- Prev/next page pagination
- Responsive design (mobile, tablet, desktop)
- Sidebar caching for performance
- Directory traversal protection

## License

MIT
