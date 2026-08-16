"use strict";

(function () {
    const initDropzones = () => {
        document.querySelectorAll("[data-cla-dropzone]").forEach((dropzone) => {
            const input = dropzone.querySelector("[data-cla-file-input]");
            const label = dropzone.querySelector("[data-cla-file-name]");

            if (!input || !label) {
                return;
            }

            const updateLabel = () => {
                if (input.files && input.files.length > 0) {
                    label.textContent = input.files[0].name;
                }
            };

            dropzone.addEventListener("dragover", (event) => {
                event.preventDefault();
                dropzone.classList.add("is-dragging");
            });

            dropzone.addEventListener("dragleave", () => {
                dropzone.classList.remove("is-dragging");
            });

            dropzone.addEventListener("drop", (event) => {
                event.preventDefault();
                dropzone.classList.remove("is-dragging");

                if (event.dataTransfer && event.dataTransfer.files.length > 0) {
                    input.files = event.dataTransfer.files;
                    updateLabel();
                }
            });

            input.addEventListener("change", updateLabel);
        });
    };

    const initFilters = () => {
        const search = document.querySelector("[data-cla-search]");
        const statusFilter = document.querySelector("[data-cla-status-filter]");
        const rows = Array.from(document.querySelectorAll("[data-cla-row]"));
        const emptyState = document.querySelector("[data-cla-filter-empty]");

        if (!rows.length || (!search && !statusFilter)) {
            return;
        }

        const applyFilters = () => {
            const query = search ? search.value.trim().toLowerCase() : "";
            const status = statusFilter ? statusFilter.value : "all";
            let visibleCount = 0;

            rows.forEach((row) => {
                const searchable = row.dataset.claSearchText || "";
                const rowStatus = row.dataset.claStatus || "";
                const matchesSearch = query === "" || searchable.includes(query);
                const matchesStatus = status === "all" || rowStatus === status;
                const isVisible = matchesSearch && matchesStatus;

                row.classList.toggle("is-hidden", !isVisible);

                if (isVisible) {
                    visibleCount += 1;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle("d-none", visibleCount > 0);
            }
        };

        if (search) {
            search.addEventListener("input", applyFilters);
        }

        if (statusFilter) {
            statusFilter.addEventListener("change", applyFilters);
        }
    };

    const copyToClipboard = async (value) => {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);

            return;
        }

        const textarea = document.createElement("textarea");
        textarea.value = value;
        textarea.setAttribute("readonly", "readonly");
        textarea.className = "visually-hidden";
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        textarea.remove();
    };

    const initCopyButtons = () => {
        document.querySelectorAll("[data-cla-copy-value]").forEach((button) => {
            button.addEventListener("click", async () => {
                const value = button.dataset.claCopyValue || "";

                if (!value) {
                    return;
                }

                await copyToClipboard(value);
                button.classList.add("is-copied");

                window.setTimeout(() => {
                    button.classList.remove("is-copied");
                }, 1200);
            });
        });
    };

    document.addEventListener("DOMContentLoaded", () => {
        initDropzones();
        initFilters();
        initCopyButtons();
    });
})();
