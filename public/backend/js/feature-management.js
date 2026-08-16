"use strict";

/**
 * Feature Management - inline toggle + search behavior.
 *
 * Every inline feature toggle intercepts its own form submission and
 * presents a confirmation dialog before actually posting the change.
 * Disabling a core feature uses the danger style variant so the admin
 * is reminded that downstream flows may break.
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        initSearch();
        initToggleForms();
    });

    function initSearch() {
        const input = document.getElementById('featureSearchInput');
        const emptyState = document.getElementById('featureSearchEmpty');

        if (!input) {
            return;
        }

        const items = Array.from(document.querySelectorAll('.feature-mgmt-col'));
        const groups = Array.from(document.querySelectorAll('.feature-mgmt-group'));

        input.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            let visible = 0;

            items.forEach(function (item) {
                const haystack = item.getAttribute('data-feature-search') || '';
                const isMatch = query === '' || haystack.indexOf(query) !== -1;
                item.classList.toggle('d-none', !isMatch);

                if (isMatch) {
                    visible += 1;
                }
            });

            groups.forEach(function (group) {
                const hasVisible = Array.from(group.querySelectorAll('.feature-mgmt-col'))
                    .some(function (col) {
                        return !col.classList.contains('d-none');
                    });
                group.classList.toggle('d-none', !hasVisible);
            });

            if (emptyState) {
                emptyState.hidden = visible !== 0;
            }
        });
    }

    function initToggleForms() {
        const backdrop = document.getElementById('featureConfirmBackdrop');
        const modal = document.getElementById('featureConfirmModal');
        const modalTitle = document.getElementById('featureConfirmTitle');
        const modalText = document.getElementById('featureConfirmText');
        const modalEyebrow = document.getElementById('featureConfirmEyebrow');
        const modalImpact = document.getElementById('featureConfirmImpact');
        const submitBtn = document.getElementById('featureConfirmSubmit');
        const submitLabel = document.getElementById('featureConfirmSubmitLabel');
        const submitIcon = document.getElementById('featureConfirmSubmitIcon');

        if (!modal || !backdrop || !submitBtn) {
            return;
        }

        let pendingForm = null;
        let pendingInput = null;

        function closeModal(revert) {
            if (revert && pendingInput) {
                pendingInput.checked = !pendingInput.checked;
            }

            modal.classList.remove('is-visible', 'is-danger');
            backdrop.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            pendingForm = null;
            pendingInput = null;
        }

        document.querySelectorAll('.feature-mgmt-toggle-form').forEach(function (form) {
            const input = form.querySelector('.feature-mgmt-switch__input');

            if (!input) {
                return;
            }

            input.addEventListener('change', function () {
                pendingForm = form;
                pendingInput = input;

                const featureLabel = form.getAttribute('data-feature-label') || 'this feature';
                const isRoleControl = form.getAttribute('data-feature-kind') === 'role';
                const isCore = form.getAttribute('data-is-core') === '1';
                const turningOn = input.checked;

                modalTitle.textContent = turningOn
                    ? 'Enable ' + featureLabel + '?'
                    : 'Disable ' + featureLabel + '?';

                if (turningOn) {
                    modalText.textContent = isRoleControl
                        ? 'Restore the role surface controlled by ' + featureLabel + '.'
                        : 'Restore ' + featureLabel + ' for panels that are already allowed.';
                    if (modalEyebrow) {
                        modalEyebrow.textContent = isRoleControl ? 'Role Control' : 'Enable';
                    }
                    if (modalImpact) {
                        modalImpact.textContent = isRoleControl
                            ? 'Registration, login, dashboards, and guarded role routes reopen immediately.'
                            : 'Menus, widgets, and protected routes reopen immediately where access is allowed.';
                    }
                    modal.classList.remove('is-danger');
                    if (submitLabel) {
                        submitLabel.textContent = 'Enable Feature';
                    } else {
                        submitBtn.textContent = 'Enable Feature';
                    }
                    if (submitIcon) {
                        submitIcon.className = 'fa-solid fa-check feature-mgmt-btn__icon';
                    }
                } else {
                    modalText.textContent = isRoleControl
                        ? 'Disable the role surface controlled by ' + featureLabel + '.'
                        : isCore
                        ? featureLabel + ' is core. Disable only if dependent flows can stop safely.'
                        : 'Hide ' + featureLabel + ' from configured panels and entry points.';
                    if (modalEyebrow) {
                        modalEyebrow.textContent = isRoleControl ? 'Role Control' : isCore ? 'Core Warning' : 'Disable';
                    }
                    if (modalImpact) {
                        modalImpact.textContent = isRoleControl
                            ? 'Registration, login, dashboards, and guarded role routes stay blocked until re-enabled.'
                            : isCore
                            ? 'Dependent journeys may become unavailable immediately.'
                            : 'Menus, widgets, and protected routes stay blocked until re-enabled.';
                    }
                    modal.classList.add('is-danger');
                    if (submitLabel) {
                        submitLabel.textContent = 'Disable Feature';
                    } else {
                        submitBtn.textContent = 'Disable Feature';
                    }
                    if (submitIcon) {
                        submitIcon.className = 'fa-solid fa-power-off feature-mgmt-btn__icon';
                    }
                }

                modal.classList.add('is-visible');
                backdrop.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        submitBtn.addEventListener('click', function () {
            if (pendingForm) {
                pendingForm.submit();
            }
        });

        document.querySelectorAll('[data-feature-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                closeModal(true);
            });
        });

        backdrop.addEventListener('click', function () {
            closeModal(true);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
                closeModal(true);
            }
        });
    }
})();
