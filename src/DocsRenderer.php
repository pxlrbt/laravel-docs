<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs;

use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class DocsRenderer
{
    public function render(DocsInstance $instance, ?string $slug = null): DocsContent
    {
        $slug ??= 'index';

        $docsRoot = $instance->rootPath();
        $filePath = "{$docsRoot}/{$slug}.md";
        $realPath = realpath($filePath);

        if ($realPath === false || ! str_starts_with($realPath, realpath($docsRoot).DIRECTORY_SEPARATOR)) {
            throw new NotFoundHttpException;
        }

        $markdown = (string) file_get_contents($realPath);

        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'docs-heading-permalink',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'before',
                'symbol' => '#',
            ],
            'table_of_contents' => [
                'html_class' => 'docs-toc',
                'position' => 'placeholder',
                'style' => 'bullet',
                'min_heading_level' => 2,
                'max_heading_level' => 3,
                'placeholder' => '[TOC]',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new FrontMatterExtension);
        $environment->addExtension(new HeadingPermalinkExtension);
        $environment->addExtension(new TableOfContentsExtension);

        $converter = new MarkdownConverter($environment);

        // Insert the [TOC] placeholder after the frontmatter block so the
        // FrontMatterExtension still finds the opening '---' at position 0.
        $markdown = (string) preg_replace('/\A(---\s*\n.*?\n---\s*\n)/s', "$1[TOC]\n\n", $markdown);

        $result = $converter->convert($markdown);

        $html = $result->getContent();

        $tableOfContents = '';
        if (preg_match('/<ul class="docs-toc">.*?<\/ul>/s', $html, $matches)) {
            $tableOfContents = $matches[0];
            $html = str_replace($matches[0], '', $html);
        }

        $frontmatter = $result instanceof RenderedContentWithFrontMatter
            ? $result->getFrontMatter()
            : [];

        $sidebar = $this->buildSidebar($instance);

        [$prevPage, $nextPage] = $this->computePagination($sidebar, $slug);

        return new DocsContent(
            html: $html,
            tableOfContents: $tableOfContents,
            frontmatter: $frontmatter,
            sidebar: $sidebar,
            currentSlug: $slug,
            instance: $instance,
            prevPage: $prevPage,
            nextPage: $nextPage,
        );
    }

    /**
     * @return array<int, array{group: string, pages: array<int, array{title: string, slug: string, order: int}>}>
     */
    private function buildSidebar(DocsInstance $instance): array
    {
        $cacheTtl = app()->isProduction() ? 300 : 60;

        return Cache::remember("docs_sidebar_{$instance->key}", $cacheTtl, function () use ($instance): array {
            $docsRoot = $instance->rootPath();
            $files = glob("{$docsRoot}/*.md") ?: [];
            $pages = [];

            foreach ($files as $file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $rawContent = (string) file_get_contents($file);

                $frontmatter = $this->extractFrontmatter($rawContent);

                if (empty($frontmatter['title']) || empty($frontmatter['group'])) {
                    continue;
                }

                $pages[] = [
                    'title' => $frontmatter['title'],
                    'slug' => $filename === 'index' ? 'index' : $filename,
                    'group' => $frontmatter['group'],
                    'order' => (int) ($frontmatter['order'] ?? 0),
                ];
            }

            usort($pages, fn (array $a, array $b): int => $a['order'] <=> $b['order']);

            $groups = [];
            foreach ($pages as $page) {
                $groups[$page['group']][] = $page;
            }

            $groupOrder = [];
            foreach ($groups as $groupName => $groupPages) {
                $groupOrder[$groupName] = min(array_column($groupPages, 'order'));
            }

            uksort($groups, fn (string $a, string $b): int => $groupOrder[$a] <=> $groupOrder[$b]);

            $sidebar = [];
            foreach ($groups as $groupName => $groupPages) {
                $sidebar[] = [
                    'group' => $groupName,
                    'pages' => array_map(fn (array $page): array => [
                        'title' => $page['title'],
                        'slug' => $page['slug'],
                        'order' => $page['order'],
                    ], $groupPages),
                ];
            }

            return $sidebar;
        });
    }

    /**
     * @param  array<int, array{group: string, pages: array<int, array{title: string, slug: string, order: int}>}>  $sidebar
     * @return array{0: array{title: string, slug: string}|null, 1: array{title: string, slug: string}|null}
     */
    private function computePagination(array $sidebar, string $currentSlug): array
    {
        $flatPages = [];
        foreach ($sidebar as $group) {
            foreach ($group['pages'] as $page) {
                $flatPages[] = ['title' => $page['title'], 'slug' => $page['slug']];
            }
        }

        $currentIndex = null;
        foreach ($flatPages as $index => $page) {
            if ($page['slug'] === $currentSlug) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null) {
            return [null, null];
        }

        $prevPage = $currentIndex > 0 ? $flatPages[$currentIndex - 1] : null;
        $nextPage = $currentIndex < count($flatPages) - 1 ? $flatPages[$currentIndex + 1] : null;

        return [$prevPage, $nextPage];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFrontmatter(string $content): array
    {
        if (! preg_match('/\A---\s*\n(.*?)\n---/s', $content, $matches)) {
            return [];
        }

        return Yaml::parse($matches[1]) ?? [];
    }
}
