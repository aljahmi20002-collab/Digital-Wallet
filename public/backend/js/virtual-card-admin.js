"use strict";

(function ($) {
    const $shell = $(".vc-admin-shell");

    if (!$shell.length) {
        return;
    }

    const i18n = {
        loading: $shell.data("vc-loading") || "Loading...",
        testing: $shell.data("vc-testing") || "Testing...",
        probing: $shell.data("vc-probing") || "Probing gateway...",
        testFailed: $shell.data("vc-test-failed") || "Test endpoint failed",
    };

    const spinner = function (label) {
        return (
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
            escapeHtml(label)
        );
    };

    const loadingBlock = function () {
        return (
            '<div class="d-flex justify-content-center py-4">' +
                '<div class="spinner-border" role="status">' +
                    '<span class="visually-hidden">' + escapeHtml(i18n.loading) + '</span>' +
                '</div>' +
            '</div>'
        );
    };

    const csrfToken = function () {
        return $('meta[name="csrf-token"]').attr("content");
    };

    const escapeHtml = function (value) {
        return $("<div>").text(value || "").html();
    };

    const initDynamicModals = function () {
        if ($("#manage-virtual-card-modal").length && typeof editFormByModal === "function") {
            editFormByModal("manage-virtual-card-modal", "manage-virtual-card-data", true, true);
        }

        if ($("#edit_fee_setting_modal").length && typeof editFormByModal === "function") {
            editFormByModal("edit_fee_setting_modal", "edit_fee_setting_data", true, true);
        }
    };

    const initGatewayCredentialModal = function () {
        const $gatewayModal = $("#edit-payment-gateway-modal");
        const $gatewayContent = $("#edit-payment-gateway-append");

        if (!$gatewayModal.length || !$gatewayContent.length) {
            return;
        }

        $(document).on("click", ".vcp-gateway-edit", function () {
            const url = $(this).data("edit-url");

            if (!url) {
                return;
            }

            $gatewayContent.html(loadingBlock());
            $gatewayModal.modal("show");

            $.get(url, function (data) {
                $gatewayContent.html(data);
                if (typeof tooltipTriger === "function") {
                    tooltipTriger();
                }
                if (typeof handleImagePreview === "function") {
                    handleImagePreview();
                }
            });
        });
    };

    const initConnectionProbe = function () {
        $(document).on("click", ".vcp-test-connection", function () {
            const $btn = $(this);
            const url = $btn.data("test-url");
            const $target = $($btn.data("result-target"));
            const originalButton = $btn.html();

            if (!url || !$target.length) {
                return;
            }

            $btn.prop("disabled", true).html(spinner(i18n.testing));
            $target
                .removeClass("d-none alert-success alert-danger alert-warning")
                .addClass("alert alert-info")
                .text(i18n.probing);

            $.ajax({
                url: url,
                type: "POST",
                headers: { "X-CSRF-TOKEN": csrfToken() },
            })
                .done(function (resp) {
                    const result = resp.result || {};
                    const provider = resp.provider || {};
                    const isOk = !!result.ok;
                    const icon = isOk ? "fa-circle-check" : "fa-circle-xmark";
                    const cls = isOk ? "alert-success" : "alert-danger";
                    const mode = result.mode ? " [" + String(result.mode).toUpperCase() + "]" : "";
                    const latency = result.latency_ms !== null && result.latency_ms !== undefined
                        ? " - " + result.latency_ms + "ms"
                        : "";

                    $target
                        .removeClass("alert-info alert-success alert-danger alert-warning")
                        .addClass("alert " + cls)
                        .html(
                            '<i class="fa-solid ' + icon + ' me-1"></i>' +
                            "<strong>" + escapeHtml(provider.name || provider.code || "Provider") + mode + "</strong>" +
                            escapeHtml(latency) + "<br>" +
                            escapeHtml(result.message || "")
                        );
                })
                .fail(function (xhr) {
                    $target
                        .removeClass("alert-info alert-success alert-danger alert-warning")
                        .addClass("alert alert-danger")
                        .html('<i class="fa-solid fa-circle-xmark me-1"></i>' + escapeHtml(i18n.testFailed) + " (" + xhr.status + ")");
                })
                .always(function () {
                    $btn.prop("disabled", false).html(originalButton);
                });
        });
    };

    const initColorPreview = function () {
        $(document).on("input", "[data-vc-color-input]", function () {
            const value = $(this).val();
            const $preview = $(this).closest(".input-group").find("[data-vc-color-preview]");

            if (/^#?([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(value || "")) {
                $preview.css("--vc-provider-preview-color", value.charAt(0) === "#" ? value : "#" + value);
                $preview.addClass("is-set");
            } else {
                $preview.removeClass("is-set");
            }
        });

        $("[data-vc-color-input]").trigger("input");

        $(document).on("shown.coreui.modal shown.bs.modal", ".modal", function () {
            $(this).find("[data-vc-color-input]").trigger("input");
        });
    };

    const initConfirmForms = function () {
        $(document).on("submit", "form[data-vc-confirm]", function () {
            const message = $(this).data("vc-confirm");

            return !message || window.confirm(message);
        });
    };

    $(function () {
        initDynamicModals();
        initGatewayCredentialModal();
        initConnectionProbe();
        initColorPreview();
        initConfirmForms();
    });
})(jQuery);
