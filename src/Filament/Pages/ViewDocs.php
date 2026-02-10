<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;
use pxlrbt\LaravelDocs\DocsContent;
use pxlrbt\LaravelDocs\DocsInstance;
use pxlrbt\LaravelDocs\DocsRenderer;
use pxlrbt\LaravelDocs\Filament\DocsPlugin;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ViewDocs extends Page
{
    protected static string $view = 'docs::filament.pages.view-docs';

    protected static ?string $slug = 'docs';

    #[Url(as: 'page', except: 'index')]
    public string $currentSlug = 'index';

    public function getTitle(): string|Htmlable
    {
        $plugin = DocsPlugin::get();

        return $plugin->getPageTitle() ?? __('docs::filament.title');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return DocsPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationLabel(): string
    {
        return DocsPlugin::get()->getNavigationLabel() ?? __('docs::filament.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return DocsPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return DocsPlugin::get()->getNavigationSort();
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::ScreenExtraLarge;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'docsContent' => $this->renderDocsContent(),
        ];
    }

    protected function renderDocsContent(): DocsContent
    {
        return once(function () {
            $plugin = DocsPlugin::get();
            $instanceKey = $plugin->getInstanceKey();

            /** @var array<string, mixed> $instanceConfig */
            $instanceConfig = config("docs.{$instanceKey}", []);
            $instance = DocsInstance::fromConfig($instanceKey, $instanceConfig);

            try {
                return app(DocsRenderer::class)->render($instance, $this->currentSlug);
            } catch (NotFoundHttpException) {
                $this->currentSlug = 'index';

                return app(DocsRenderer::class)->render($instance, 'index');
            }
        });
    }

    public function navigateTo(string $slug): void
    {
        $this->currentSlug = $slug;
    }
}
