'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('pageComponentSearch');
    const rows = Array.from(document.querySelectorAll('[data-component-row]'));
    const emptyState = document.querySelector('[data-component-empty]');
    const resultsCount = document.querySelector('[data-component-results-count]');

    if (!searchInput || rows.length === 0) {
        return;
    }

    const updateResults = () => {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const haystack = (row.dataset.search || '').toLowerCase();
            const matches = query === '' || haystack.includes(query);

            row.classList.toggle('d-none', !matches);

            if (matches) {
                visibleCount += 1;
            }
        });

        if (resultsCount) {
            resultsCount.textContent = String(visibleCount);
        }

        if (emptyState) {
            emptyState.classList.toggle('d-none', visibleCount !== 0);
        }
    };

    searchInput.addEventListener('input', updateResults);
    updateResults();
});
