<style>
    :root { --docs-nav-top: 4rem; }
</style>

@php
    $instance = $docsContent->instance;
    $sidebar = $docsContent->sidebar;
    $currentSlug = $docsContent->currentSlug;
    $content = $docsContent->html;
    $tableOfContents = $docsContent->tableOfContents;
    $prevPage = $docsContent->prevPage;
    $nextPage = $docsContent->nextPage;
@endphp

{{-- Mobile Navigation --}}
@if($instance->hasNavigation)
    <div class="docs-mobile-nav">
        <select wire:change="navigateTo($event.target.value)">
            @foreach ($sidebar as $group)
                <optgroup label="{{ $group['group'] }}">
                    @foreach ($group['pages'] as $page)
                        <option
                            value="{{ $page['slug'] }}"
                            @if($currentSlug === $page['slug']) selected @endif
                        >
                            {{ $page['title'] }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
@endif

<div class="docs-layout {{ $instance->hasNavigation ? '' : 'docs-layout--no-nav' }} {{ $instance->hasSidebar ? '' : 'docs-layout--no-toc' }}">

    {{-- Desktop Sidebar --}}
    @if($instance->hasNavigation)
        <nav class="docs-sidebar">
            @foreach ($sidebar as $group)
                <div class="docs-sidebar-group">
                    <p class="docs-sidebar-group-title">{{ $group['group'] }}</p>
                    <ul class="docs-sidebar-list">
                        @foreach ($group['pages'] as $page)
                            @php
                                $isActive = $currentSlug === $page['slug'];
                            @endphp
                            <li>
                                <a
                                    href="#"
                                    wire:click.prevent="navigateTo('{{ $page['slug'] }}')"
                                    class="docs-sidebar-link {{ $isActive ? 'docs-sidebar-link--active' : '' }}"
                                >
                                    {{ $page['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    @endif

    {{-- Content --}}
    <article class="docs-content">
        <div class="docs-prose">
            {!! $content !!}
        </div>

        {{-- Pagination --}}
        @if($instance->hasPagination && ($prevPage || $nextPage))
            <nav class="docs-pagination">
                @if($prevPage)
                    <a href="#" wire:click.prevent="navigateTo('{{ $prevPage['slug'] }}')">
                        &larr; {{ $prevPage['title'] }}
                    </a>
                @else
                    <span class="docs-pagination-spacer"></span>
                @endif

                @if($nextPage)
                    <a href="#" wire:click.prevent="navigateTo('{{ $nextPage['slug'] }}')">
                        {{ $nextPage['title'] }} &rarr;
                    </a>
                @endif
            </nav>
        @endif
    </article>

    {{-- Table of Contents (right sidebar) --}}
    @if($instance->hasSidebar && $tableOfContents)
        <aside class="docs-toc-aside">
            <p class="docs-toc-title">Auf dieser Seite</p>
            <div class="docs-toc-nav">
                {!! $tableOfContents !!}
            </div>
        </aside>
    @endif
</div>
