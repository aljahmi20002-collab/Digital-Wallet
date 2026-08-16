"use strict";

(function () {
    const scrollAreaSelector = ".settings-site-scrollarea, .settings-site-sidebar__nav";

    function canScroll(element) {
        return element.scrollHeight > element.clientHeight + 1;
    }

    function isAtTop(element) {
        return element.scrollTop <= 0;
    }

    function isAtBottom(element) {
        return Math.ceil(element.scrollTop + element.clientHeight) >= element.scrollHeight;
    }

    document.addEventListener("wheel", function (event) {
        const scrollArea = event.target.closest(scrollAreaSelector);

        if (!scrollArea || !canScroll(scrollArea) || event.deltaY === 0) {
            return;
        }

        const shouldScrollPage =
            (event.deltaY < 0 && isAtTop(scrollArea)) ||
            (event.deltaY > 0 && isAtBottom(scrollArea));

        if (!shouldScrollPage) {
            return;
        }

        event.preventDefault();
        window.scrollBy({
            top: event.deltaY,
            left: 0,
            behavior: "auto",
        });
    }, { passive: false });
})();

(function () {
    const modalElement = document.querySelector("[data-settings-enable-warning-modal]");
    const modalTitle = modalElement ? modalElement.querySelector("[data-settings-warning-title]") : null;
    const modalMessage = modalElement ? modalElement.querySelector("[data-settings-warning-message]") : null;
    const modalSecret = modalElement ? modalElement.querySelector("[data-settings-warning-secret]") : null;
    const modalConfirmButton = modalElement ? modalElement.querySelector("[data-settings-enable-warning-confirm]") : null;
    const modalConfirmLabel = modalElement ? modalElement.querySelector("[data-settings-enable-warning-confirm-label]") : null;
    const copySecretButton = modalElement ? modalElement.querySelector("[data-settings-copy-secret]") : null;
    const copySecretLabel = modalElement ? modalElement.querySelector("[data-settings-copy-secret-label]") : null;
    const defaultCopyLabel = copySecretLabel ? copySecretLabel.textContent : "";
    let pendingSwitch = null;

    function getWarningModal() {
        if (!modalElement || !window.coreui || !window.coreui.Modal) {
            return null;
        }

        return window.coreui.Modal.getOrCreateInstance(modalElement);
    }

    function currentSecretKey() {
        const secretInput = document.querySelector('input[name="secret_key"]');
        const secretValue = secretInput ? secretInput.value.trim() : "";

        return secretValue || (modalSecret ? modalSecret.textContent.trim() : "");
    }

    function writeClipboard(value) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(value);
        }

        const textarea = document.createElement("textarea");
        textarea.value = value;
        textarea.setAttribute("readonly", "");
        textarea.style.position = "fixed";
        textarea.style.opacity = "0";
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        textarea.remove();

        return Promise.resolve();
    }

    function resetCopyLabel() {
        if (copySecretLabel) {
            copySecretLabel.textContent = defaultCopyLabel;
        }
    }

    function fillWarningModal(switchInput) {
        if (modalTitle) {
            modalTitle.textContent = switchInput.dataset.settingsEnableWarningTitle || modalTitle.textContent;
        }

        if (modalMessage) {
            modalMessage.textContent = switchInput.dataset.settingsEnableWarning || modalMessage.textContent;
        }

        if (modalConfirmLabel) {
            modalConfirmLabel.textContent = switchInput.dataset.settingsEnableWarningConfirm || modalConfirmLabel.textContent;
        }

        if (modalSecret) {
            modalSecret.textContent = currentSecretKey();
        }

        resetCopyLabel();
    }

    document.querySelectorAll("[data-settings-enable-warning]").forEach(function (switchInput) {
        switchInput.addEventListener("change", function () {
            if (!switchInput.checked) {
                return;
            }

            switchInput.checked = false;
            pendingSwitch = switchInput;
            fillWarningModal(switchInput);

            const warningModal = getWarningModal();

            if (warningModal) {
                warningModal.show();
                return;
            }

            pendingSwitch.checked = true;
            pendingSwitch = null;
        });
    });

    if (modalConfirmButton) {
        modalConfirmButton.addEventListener("click", function () {
            const warningModal = getWarningModal();

            if (pendingSwitch) {
                pendingSwitch.checked = true;
                pendingSwitch.focus({ preventScroll: true });
                pendingSwitch = null;
            }

            if (warningModal) {
                warningModal.hide();
            }
        });
    }

    if (copySecretButton) {
        copySecretButton.addEventListener("click", function () {
            const secretKey = currentSecretKey();

            if (!secretKey) {
                return;
            }

            writeClipboard(secretKey).then(function () {
                if (copySecretLabel) {
                    copySecretLabel.textContent = copySecretButton.dataset.settingsCopySuccess || defaultCopyLabel;
                }
            });
        });
    }

    if (modalElement) {
        modalElement.addEventListener("hidden.coreui.modal", function () {
            pendingSwitch = null;
            resetCopyLabel();
        });
    }
})();
