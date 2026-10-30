(() => {
    const eligibleInputTypes = new Set([
        'text', 'email', 'tel', 'url', 'search', 'number',
    ]);

    const humanize = (value) => String(value || '')
        .replace(/\[\]/g, ' ')
        .replace(/\[[^\]]*\]/g, ' ')
        .replace(/[_-]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

    const fieldLabel = (field) => {
        const label = field.labels?.[0]?.textContent
            ?.replace(/[＊*]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/[:：]+$/, '');

        return label || field.getAttribute('aria-label') || humanize(field.name);
    };

    const addFieldHint = (field) => {
        const label = fieldLabel(field);
        if (!label) return;

        if (field instanceof HTMLSelectElement) {
            const firstOption = field.options[0];
            const alreadyHasPrompt = firstOption && firstOption.value === '';
            const hasExplicitSelection = Array.from(field.options).some((option) => option.hasAttribute('selected'));
            if (!alreadyHasPrompt && !hasExplicitSelection) {
                const prompt = new Option(`Select ${label}`, '', true, true);
                prompt.disabled = true;
                field.insertBefore(prompt, firstOption || null);
            }
            return;
        }

        if (field instanceof HTMLTextAreaElement) {
            if (!field.getAttribute('placeholder')?.trim()) {
                field.setAttribute('placeholder', `Enter ${label.toLowerCase()}`);
            }
            return;
        }

        if (field instanceof HTMLInputElement) {
            const type = (field.type || 'text').toLowerCase();
            if (eligibleInputTypes.has(type) && !field.getAttribute('placeholder')?.trim()) {
                field.setAttribute('placeholder', `Enter ${label.toLowerCase()}`);
            }
        }
    };

    const enhanceFormFields = (root = document) => {
        root.querySelectorAll('input, textarea, select').forEach(addFieldHint);
    };

    const start = () => {
        enhanceFormFields();

        // Support modal forms and repeated form rows added after page load.
        new MutationObserver((mutations) => {
            mutations.forEach(({ addedNodes }) => addedNodes.forEach((node) => {
                if (node.nodeType !== Node.ELEMENT_NODE) return;
                if (node.matches?.('input, textarea, select')) addFieldHint(node);
                enhanceFormFields(node);
            }));
        }).observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
