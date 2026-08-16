"use strict";

$(document).ready(function () {
    if ($("#edit-payment-gateway-modal").length && typeof editFormByModal === "function") {
        editFormByModal("edit-payment-gateway-modal", "edit-payment-gateway-append", false, true);
    }
});

$(document).on("click", "[data-gateway-test]", function () {
    const button = $(this);
    const form = button.closest("form");
    const result = form.find("[data-gateway-test-result]");
    const label = button.find("[data-gateway-test-label]");
    const icon = button.find(".pgm-test-btn__icon i");
    const defaultLabel = label.text();
    const defaultIconClass = icon.attr("class");
    const testUrl = button.data("test-url");
    const testingLabel = button.data("testing-label") || "Checking...";
    const testingMessage = button.data("testing-message") || "Validating gateway credentials...";
    const fallbackMessage = button.data("fallback-message") || "Unable to validate this gateway right now.";

    if (!testUrl || button.prop("disabled")) {
        return;
    }

    button.prop("disabled", true).addClass("is-loading");
    icon.attr("class", "fa-solid fa-spinner fa-spin");
    label.text(testingLabel);
    result.removeClass("d-none alert-success alert-warning alert-danger")
        .addClass("alert alert-info")
        .text(testingMessage);

    $.ajax({
        url: testUrl,
        method: "POST",
        data: $.param(form.serializeArray().filter(function (field) {
            return field.name !== "_method";
        })),
        success: function (response) {
            const status = response.status === "success"
                ? "success"
                : (response.status === "warning" ? "warning" : "danger");

            result.removeClass("alert-info alert-success alert-warning alert-danger")
                .addClass("alert-" + status)
                .text(response.message || "Gateway test completed.");
        },
        error: function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : fallbackMessage;

            result.removeClass("alert-info alert-success alert-warning alert-danger")
                .addClass("alert-danger")
                .text(message);
        },
        complete: function () {
            button.prop("disabled", false).removeClass("is-loading");
            icon.attr("class", defaultIconClass);
            label.text(defaultLabel);
        },
    });
});
