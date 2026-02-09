<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs;

final readonly class DocsContent
{
    /**
     * @param  array<string, mixed>  $frontmatter
     * @param  array<int, array{group: string, pages: array<int, array{title: string, slug: string, order: int}>}>  $sidebar
     * @param  array{title: string, slug: string}|null  $prevPage
     * @param  array{title: string, slug: string}|null  $nextPage
     */
    public function __construct(
        public string $html,
        public string $tableOfContents,
        public array $frontmatter,
        public array $sidebar,
        public string $currentSlug,
        public DocsInstance $instance,
        public ?array $prevPage,
        public ?array $nextPage,
    ) {}
}
