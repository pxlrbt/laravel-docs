@if($instance->layout)
    <x-dynamic-component
        :component="$instance->layout"
        :title="$frontmatter['title'] ?? 'Documentation'"
        :description="$frontmatter['description'] ?? 'Documentation'"
        :showFooterCta="false"
    >
        <div class="docs-wrapper">
            @include('docs::partials.body')
        </div>
    </x-dynamic-component>
@else
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $frontmatter['title'] ?? 'Documentation' }}</title>
        <link rel="stylesheet" href="{{ asset('vendor/docs/docs.css') }}">
    </head>
    <body class="docs-standalone">
        @include('docs::partials.body')
    </body>
    </html>
@endif
