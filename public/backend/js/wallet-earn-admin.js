"use strict";

(function ($) {
    const sortableTarget = document.getElementById("wallet-earn-plan-sortable");

    if (!sortableTarget || typeof Sortable === "undefined" || !sortableTarget.querySelector("tr[data-id]")) {
        return;
    }

    const endpoint = sortableTarget.dataset.positionUrl;
    const csrfToken = sortableTarget.dataset.csrfToken;
    const successMessage = sortableTarget.dataset.successMessage || "Plan order updated successfully.";
    const errorMessage = sortableTarget.dataset.errorMessage || "Unable to update plan order right now.";

    if (!endpoint || !csrfToken) {
        return;
    }

    new Sortable(sortableTarget, {
        animation: 150,
        handle: ".drag-handle",
        ghostClass: "bg-light",
        onEnd: function () {
            const positions = [];

            $("#wallet-earn-plan-sortable tr").each(function (index) {
                const id = $(this).data("id");

                if (id) {
                    positions.push({
                        id: id,
                        order: index + 1,
                    });
                }
            });

            if (!positions.length) {
                return;
            }

            $.ajax({
                url: endpoint,
                method: "POST",
                data: {
                    _token: csrfToken,
                    positions: positions,
                },
                success: function (response) {
                    notifyEvs("success", response.message || successMessage);
                },
                error: function () {
                    notifyEvs("error", errorMessage);
                },
            });
        },
    });
})(jQuery);
