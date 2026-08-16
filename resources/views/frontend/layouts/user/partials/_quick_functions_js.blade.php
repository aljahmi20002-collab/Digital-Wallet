<script>
    "use strict";
    document.addEventListener('DOMContentLoaded', function() {
        const quickFunctionTrigger = document.getElementById("{{ $btnId ?? 'quickFunctionBtn' }}");
        const quickFunctionMenu = document.getElementById("{{ $dropdownId ?? 'quickFunctionDropdown' }}");
        const quickFunctionScrollArea = quickFunctionMenu ? quickFunctionMenu.querySelector('.quick-function-menu__body') : null;
        const quickFunctionClose = quickFunctionMenu ? quickFunctionMenu.querySelector('[data-quick-function-close]') : null;
        let quickFunctionTouchY = null;

        if (!quickFunctionTrigger || !quickFunctionMenu) {
            return;
        }

        const setQuickFunctionMenuLock = function(isLocked) {
            document.body.classList.toggle('quick-function-menu-open', isLocked);
        };

        const closeQuickFunctionMenu = function() {
            quickFunctionMenu.classList.remove('show');
            quickFunctionTrigger.setAttribute('aria-expanded', 'false');
            setQuickFunctionMenuLock(false);
        };

        const openQuickFunctionMenu = function() {
            quickFunctionMenu.classList.add('show');
            quickFunctionTrigger.setAttribute('aria-expanded', 'true');
            setQuickFunctionMenuLock(true);
        };

        const scrollQuickFunctionMenu = function(deltaY) {
            if (!quickFunctionScrollArea) {
                return;
            }

            quickFunctionScrollArea.scrollBy({
                top: deltaY,
                behavior: 'smooth',
            });
        };

        quickFunctionTrigger.setAttribute('aria-expanded', 'false');

        quickFunctionTrigger.addEventListener('click', function(e) {
            e.stopPropagation();

            if (quickFunctionMenu.classList.contains('show')) {
                closeQuickFunctionMenu();

                return;
            }

            openQuickFunctionMenu();
        });

        document.addEventListener('click', function(e) {
            if (!quickFunctionMenu.contains(e.target) && !quickFunctionTrigger.contains(e.target)) {
                closeQuickFunctionMenu();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQuickFunctionMenu();
            }
        });

        quickFunctionMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        if (quickFunctionClose) {
            quickFunctionClose.addEventListener('click', function(e) {
                e.stopPropagation();
                closeQuickFunctionMenu();
            });
        }

        quickFunctionMenu.addEventListener('wheel', function(e) {
            if (!quickFunctionScrollArea) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            const deltaMultiplier = e.deltaMode === 1 ? 16 : 1;
            scrollQuickFunctionMenu(e.deltaY * deltaMultiplier);
        }, { passive: false });

        quickFunctionMenu.addEventListener('touchstart', function(e) {
            quickFunctionTouchY = e.touches.length ? e.touches[0].clientY : null;
        }, { passive: true });

        quickFunctionMenu.addEventListener('touchmove', function(e) {
            if (quickFunctionTouchY === null || !e.touches.length) {
                return;
            }

            const nextY = e.touches[0].clientY;
            scrollQuickFunctionMenu(quickFunctionTouchY - nextY);
            quickFunctionTouchY = nextY;
            e.preventDefault();
            e.stopPropagation();
        }, { passive: false });
    });
</script>
