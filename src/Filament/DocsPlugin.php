<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use pxlrbt\LaravelDocs\Filament\Pages\ViewDocs;

class DocsPlugin implements Plugin
{

    protected static string $id = 'docs';

    protected string $instance = 'default';

    protected string $navigationIcon = 'heroicon-o-book-open';

    protected ?string $navigationLabel = 'Documentation';

    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    protected ?string $pageTitle = null;

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return DocsPlugin
     */
    public static function get(): Plugin
    {
        return filament()->getCurrentPanel()->getPlugin(static::$id);
    }

    public function getId(): string
    {
        return static::$id;
    }

    public function instance(string $instance): static
    {
        $this->instance = $instance;

        return $this;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function navigationLabel(string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function pageTitle(?string $title): static
    {
        $this->pageTitle = $title;

        return $this;
    }

    public function getInstanceKey(): string
    {
        return $this->instance;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon;
    }

    public function getNavigationLabel(): ?string
    {
        return $this->navigationLabel;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            ViewDocs::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render('<link rel="stylesheet" href="{{ asset(\'vendor/docs/docs.css\') }}">'),
        );
    }
}
