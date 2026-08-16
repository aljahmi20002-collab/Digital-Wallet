<script>
"use strict";
(function() {
    const toggleBtn = document.getElementById('amountCardsToggle');
    const grid = document.getElementById('amountCardsRow');
    if (!toggleBtn || !grid) return;

    function setExpanded(expanded) {
        if (expanded) {
            grid.setAttribute('data-expanded', '1');
            toggleBtn.setAttribute('data-expanded', '1');
            toggleBtn.innerHTML = `${toggleBtn.getAttribute('data-less-label') || '{{ __('Show less') }}'} <i class="fa fa-angle-up ms-1"></i>`;
        } else {
            grid.removeAttribute('data-expanded');
            toggleBtn.setAttribute('data-expanded', '0');
            toggleBtn.innerHTML = `${toggleBtn.getAttribute('data-more-label') || '{{ __('Show more') }}'} <i class="fa fa-angle-down ms-1"></i>`;
            // keep context visible when collapsing
            const wrapper = grid.closest('.amount-wrapper');
            if (wrapper && 'scrollIntoView' in wrapper) {
                wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    // labels
    toggleBtn.setAttribute('data-more-label', toggleBtn.textContent.trim());
    toggleBtn.setAttribute('data-less-label', '{{ __('See less') }}');

    // default collapsed
    setExpanded(false);

    toggleBtn.addEventListener('click', function() {
        const expanded = this.getAttribute('data-expanded') === '1';
        setExpanded(!expanded);
    });
})();

(function() {
    "use strict";
    const qa = document.querySelector('.quick-actions[data-collapsible]');
    if (!qa) return;
    const wrapper = qa.closest('.qa-wrapper');
    const overlay = wrapper ? wrapper.querySelector('.qa-overlay') : null;
    const btn = overlay ? overlay.querySelector('.qa-see-more') : null;
    if (!overlay || !btn) return;

    const items = qa.querySelectorAll('.qa-item');
    const limit = 8;

    function setExpanded(expanded) {
        if (expanded) {
            qa.setAttribute('data-expanded', '1');
            btn.setAttribute('data-expanded', '1');
            btn.innerHTML = `${btn.getAttribute('data-less-label') || '{{ __('See less') }}'} <i class="fa fa-angle-up ms-1"></i>`;
        } else {
            qa.removeAttribute('data-expanded');
            btn.setAttribute('data-expanded', '0');
            btn.innerHTML = `${btn.getAttribute('data-more-label') || '{{ __('See more') }}'} <i class="fa fa-angle-down ms-1"></i>`;
            // keep context visible when collapsing
            if (wrapper && 'scrollIntoView' in wrapper) {
                wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    // initialize labels
    btn.setAttribute('data-more-label', btn.textContent.trim());
    btn.setAttribute('data-less-label', '{{ __('See less') }}');

    // hide overlay if items are less than/equal to limit
    if (items.length <= limit) {
        setExpanded(true);
        overlay.style.display = 'none';
        return;
    }

    // collapsed by default
    setExpanded(false);

    btn.addEventListener('click', function() {
        const expanded = this.getAttribute('data-expanded') === '1';
        setExpanded(!expanded);
    });
})();
</script>
