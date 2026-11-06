// Renders ```mermaid blocks. The bundled Mermaid (no CDN) is only loaded when a page contains a diagram.
const MERMAID_URL = new URL('mermaid.min.js', import.meta.url);

let mermaidPromise = null;
let diagramCount = 0;

function loadMermaid() {
    mermaidPromise ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = MERMAID_URL;
        script.onload = () => resolve(window.mermaid);
        script.onerror = reject;
        document.head.append(script);
    }).then((mermaid) => {
        const isDark = document.documentElement.classList.contains('dark');

        // Mirrors the colour tokens in docs.css; Mermaid needs plain hex values.
        const colors = isDark
            ? { node: '#1e293b', border: '#3b82f6', text: '#f1f5f9', line: '#64748b', label: '#a3aebf', background: '#0f172a' }
            : { node: '#eff6ff', border: '#93c5fd', text: '#111827', line: '#9ca3af', label: '#4b5563', background: '#ffffff' };

        mermaid.initialize({
            startOnLoad: false,
            theme: 'base',
            fontFamily: getComputedStyle(document.body).fontFamily,
            flowchart: { useMaxWidth: false, curve: 'basis', padding: 12, nodeSpacing: 40, rankSpacing: 40, wrappingWidth: 280 },
            themeVariables: {
                darkMode: isDark,
                fontSize: '14px',
                background: colors.background,
                primaryColor: colors.node,
                primaryBorderColor: colors.border,
                primaryTextColor: colors.text,
                secondaryColor: colors.node,
                tertiaryColor: colors.node,
                lineColor: colors.line,
                textColor: colors.label,
                edgeLabelBackground: colors.background,
                clusterBkg: colors.background,
                clusterBorder: colors.line,
            },
        });

        return mermaid;
    });

    return mermaidPromise;
}

async function renderDiagrams() {
    const diagrams = document.querySelectorAll('.docs-mermaid:not([data-rendered])');

    if (diagrams.length === 0) {
        return;
    }

    const mermaid = await loadMermaid();

    for (const diagram of diagrams) {
        diagram.dataset.rendered = '';

        try {
            const { svg } = await mermaid.render(`docs-mermaid-${++diagramCount}`, diagram.textContent);
            diagram.innerHTML = svg;
        } catch (error) {
            diagram.dataset.error = '';
            console.error('Mermaid diagram could not be rendered', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', renderDiagrams);

// Filament: docs pages are swapped via Livewire without a page load.
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => requestAnimationFrame(renderDiagrams));
    });
});

// Lightbox: clicking an image or video in the content opens it in a <dialog> together with all other media of the page.
let lightbox = null;
let gallery = [];
let galleryIndex = 0;

const icon = (path) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${path}"/></svg>`;

function createLightbox() {
    const dialog = document.createElement('dialog');
    dialog.className = 'docs-lightbox';
    // Focusing the dialog itself instead of its first button avoids a focus ring on the close button.
    dialog.tabIndex = -1;
    dialog.innerHTML = `
        <div class="docs-lightbox-stage">
            <div class="docs-lightbox-media"></div>
            <div class="docs-lightbox-info">
                <p class="docs-lightbox-caption"></p>
                <span class="docs-lightbox-counter"></span>
            </div>
        </div>
        <button type="button" class="docs-lightbox-close" aria-label="Close">${icon('M6 6l12 12M18 6L6 18')}</button>
        <button type="button" class="docs-lightbox-prev" aria-label="Previous">${icon('M15 18l-6-6 6-6')}</button>
        <button type="button" class="docs-lightbox-next" aria-label="Next">${icon('M9 18l6-6-6-6')}</button>
    `;

    dialog.querySelector('.docs-lightbox-close').addEventListener('click', () => dialog.close());
    dialog.querySelector('.docs-lightbox-prev').addEventListener('click', () => showMedia(galleryIndex - 1));
    dialog.querySelector('.docs-lightbox-next').addEventListener('click', () => showMedia(galleryIndex + 1));
    // Clicks next to the media close the lightbox.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.matches('.docs-lightbox-stage, .docs-lightbox-media')) {
            dialog.close();
        }
    });
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            showMedia(galleryIndex - 1);
        }

        if (event.key === 'ArrowRight') {
            showMedia(galleryIndex + 1);
        }
    });
    // Removing the media also stops a playing video.
    dialog.addEventListener('close', () => dialog.querySelector('.docs-lightbox-media').replaceChildren());

    document.body.append(dialog);

    return dialog;
}

function showMedia(index) {
    galleryIndex = (index + gallery.length) % gallery.length;
    const source = gallery[galleryIndex];
    const media = source.tagName === 'VIDEO'
        ? Object.assign(document.createElement('video'), { src: source.currentSrc || source.src, poster: source.poster, controls: true, autoplay: true, playsInline: true })
        : Object.assign(document.createElement('img'), { src: source.currentSrc || source.src, alt: source.alt });

    lightbox.querySelector('.docs-lightbox-media').replaceChildren(media);
    lightbox.querySelector('.docs-lightbox-caption').textContent = source.closest('figure')?.querySelector('figcaption')?.textContent.trim() ?? '';
    lightbox.querySelector('.docs-lightbox-counter').textContent = `${galleryIndex + 1} / ${gallery.length}`;
    lightbox.toggleAttribute('data-single', gallery.length === 1);
}

const isLightboxMedia = (element) => ! element.closest('a, .docs-mermaid');

// Delegated, so it keeps working after Livewire swaps the page content.
document.addEventListener('click', (event) => {
    const source = event.target.closest('.docs-prose img, .docs-prose video');

    if (! source || ! isLightboxMedia(source)) {
        return;
    }

    event.preventDefault();
    if (source.tagName === 'VIDEO') {
        source.pause();
    }

    gallery = [...source.closest('.docs-prose').querySelectorAll('img, video')].filter(isLightboxMedia);
    lightbox ??= createLightbox();
    showMedia(gallery.indexOf(source));
    lightbox.showModal();
    lightbox.focus();
});
