<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs;

use Closure;
use Illuminate\Http\Request;

class DocsInstance
{
    /**
     * @param  array<string>  $authMiddleware
     * @param  (Closure(Request): bool)|null  $canAccessDocs
     */
    public function __construct(
        public readonly string $key,
        public readonly string $root,
        public readonly string $urlPrefix,
        public readonly ?string $layout,
        public readonly bool $requiresAuth,
        public readonly array $authMiddleware,
        public readonly ?Closure $canAccessDocs,
        public readonly bool $hasNavigation,
        public readonly bool $hasSidebar,
        public readonly bool $hasPagination,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(string $key, array $config): self
    {
        $canAccessDocs = $config['can_access_docs'] ?? null;

        return new self(
            key: $key,
            root: (string) ($config['root'] ?? 'docs'),
            urlPrefix: (string) ($config['url_prefix'] ?? 'docs'),
            layout: $config['layout'] ?? null,
            requiresAuth: (bool) ($config['requires_auth'] ?? false),
            authMiddleware: (array) ($config['auth_middleware'] ?? ['auth']),
            canAccessDocs: $canAccessDocs instanceof Closure ? $canAccessDocs : null,
            hasNavigation: (bool) ($config['has_navigation'] ?? true),
            hasSidebar: (bool) ($config['has_sidebar'] ?? true),
            hasPagination: (bool) ($config['has_pagination'] ?? true),
        );
    }

    public function rootPath(): string
    {
        if (str_starts_with($this->root, DIRECTORY_SEPARATOR)) {
            return $this->root;
        }

        return base_path($this->root);
    }

    public function routeName(string $suffix = 'show'): string
    {
        return "docs.{$this->key}.{$suffix}";
    }
}
