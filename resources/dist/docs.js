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
