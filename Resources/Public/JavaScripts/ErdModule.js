/**
 * ERD backend module JS
 */
document.addEventListener('DOMContentLoaded', function () {
    // Mode toggle: show/hide extension vs table selector
    const modeSelect = document.getElementById('mode');
    if (modeSelect) {
        modeSelect.addEventListener('change', function () {
            document.getElementById('ext-select').style.display = this.value === 'extension' ? 'block' : 'none';
            document.getElementById('table-select').style.display = this.value === 'tables' ? 'block' : 'none';
        });
    }

    // Copy buttons on generate view
    document.getElementById('copy-mermaid-btn')?.addEventListener('click', function () {
        const source = document.getElementById('mermaid-source').textContent;
        navigator.clipboard.writeText(source).then(function () {
            alert('Mermaid code copied to clipboard');
        });
    });

    document.getElementById('copy-markdown-btn')?.addEventListener('click', function () {
        const source = document.getElementById('markdown-source').value;
        navigator.clipboard.writeText(source).then(function () {
            alert('Markdown copied to clipboard');
        });
    });
});
