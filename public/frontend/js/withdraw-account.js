"use strict";

$(document).ready(function () {
    const form = document.querySelector("[data-withdraw-account-form]");

    if (!form) {
        return;
    }

    const methodSelect = form.querySelector("#method-select");
    const accountName = form.querySelector("#accountName");
    const credentialFields = form.querySelector("#credential-fields");
    const urlTemplate = form.dataset.fieldsUrlTemplate || "";
    const loadingText = form.dataset.loadingText || "Loading credential fields...";
    const errorText = form.dataset.errorText || "Unable to load credential fields.";

    function setCredentialMessage(message, stateClass) {
        credentialFields.innerHTML = [
            '<div class="col-12">',
            '<div class="withdraw-account-field-state ' + stateClass + '">',
            message,
            "</div>",
            "</div>",
        ].join("");
    }

    function fieldsUrl(methodId) {
        return urlTemplate.replace("__METHOD_ID__", encodeURIComponent(methodId));
    }

    $(methodSelect).on("change", function () {
        const methodId = this.value;

        if (!methodId || !urlTemplate) {
            credentialFields.innerHTML = "";
            return;
        }

        setCredentialMessage(loadingText, "is-loading");

        $.ajax({
            url: fieldsUrl(methodId),
            type: "GET",
            success: function (data) {
                credentialFields.innerHTML = data.html || "";

                if (!accountName.value && data.method_name) {
                    accountName.value = data.method_name;
                }
            },
            error: function () {
                setCredentialMessage(errorText, "is-error");
            },
        });
    });
});
