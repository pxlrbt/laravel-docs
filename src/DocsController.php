<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class DocsController extends Controller
{
    public function show(Request $request, ?string $slug = null): View|Response
    {
        $instanceKey = $request->route()?->getAction('docsInstance') ?? 'default';

        /** @var array<string, mixed> $instanceConfig */
        $instanceConfig = config("docs.{$instanceKey}", []);
        $instance = DocsInstance::fromConfig($instanceKey, $instanceConfig);

        if ($instance->canAccessDocs !== null && ! ($instance->canAccessDocs)($request)) {
            abort(403);
        }

        $content = app(DocsRenderer::class)->render($instance, $slug);

        /** @var view-string $viewName */
        $viewName = 'docs::show';

        return view($viewName, [
            'content' => $content->html,
            'tableOfContents' => $content->tableOfContents,
            'frontmatter' => $content->frontmatter,
            'sidebar' => $content->sidebar,
            'currentSlug' => $content->currentSlug,
            'instance' => $content->instance,
            'prevPage' => $content->prevPage,
            'nextPage' => $content->nextPage,
            'routeName' => $instance->routeName(),
        ]);
    }
}
