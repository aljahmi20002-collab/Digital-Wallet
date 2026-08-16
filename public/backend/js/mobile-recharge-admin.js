"use strict";

$(document).ready(function () {
    const modal = $("#manage-mobile-recharge-provider-modal");
    const modalContent = $("#manage-mobile-recharge-provider-data");

    if (!modal.length || !modalContent.length) {
        return;
    }

    $(document).on("click", ".mra-provider-manage-modal", function () {
        const url = $(this).data("edit-url");

        if (!url) {
            return;
        }

        modal.modal("show");
        modalContent.html(
            '<div class="d-flex justify-content-center py-4">' +
                '<div class="spinner-border" role="status">' +
                    '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
            '</div>'
        );

        $.get(url, function (data) {
            modalContent.html(data);

            if (typeof tooltipTriger === "function") {
                tooltipTriger();
            }

            if (typeof handleImagePreview === "function") {
                handleImagePreview();
            }
        });
    });
});
