<script>
    'use strict'
    $(document).ready(function () {
        if ($("#manageModal").length && typeof editFormByModal === "function") {
            editFormByModal("manageModal", "edit-append", false, true);
        }
    });

    $(document).on("click", "[data-plugin-test]", function () {
        const button = $(this);
        const form = button.closest("form");
        const result = form.find("[data-plugin-test-result]");
        const label = button.find("[data-plugin-test-label]");
        const icon = button.find(".settings-plugin-test-card__button-icon i");
        const defaultLabel = label.text();
        const defaultIconClass = icon.attr("class");
        const testUrl = button.data("test-url");
        const testingLabel = button.data("testing-label") || "Testing...";
        const testingMessage = button.data("testing-message") || "Testing provider connection...";
        const fallbackMessage = button.data("fallback-message") || "Unable to test this provider right now.";

        if (!testUrl || button.prop("disabled")) {
            return;
        }

        button.prop("disabled", true).addClass("is-loading");
        icon.attr("class", "fa-solid fa-spinner fa-spin");
        label.text(testingLabel);
        result.removeClass("d-none is-success is-warning is-error")
            .addClass("is-info")
            .text(testingMessage);

        $.ajax({
            url: testUrl,
            method: "POST",
            data: $.param(form.serializeArray().filter(function (field) {
                return field.name !== "_method";
            })),
            success: function (response) {
                const status = response.status === "success"
                    ? "is-success"
                    : (response.status === "warning" ? "is-warning" : "is-error");

                result.removeClass("is-info is-success is-warning is-error")
                    .addClass(status)
                    .text(response.message || "Provider connection test completed.");
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : fallbackMessage;

                result.removeClass("is-info is-success is-warning is-error")
                    .addClass("is-error")
                    .text(message);
            },
            complete: function () {
                button.prop("disabled", false).removeClass("is-loading");
                icon.attr("class", defaultIconClass);
                label.text(defaultLabel);
            },
        });
    });
</script>
