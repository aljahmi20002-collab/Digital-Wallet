"use strict";

$(document).ready(function () {
    const $paymentCards = $(".payment-logo-card"),
        $payButton = $("#payButton"),
        $selectedMethodInput = $("#selectedMethod");

    $paymentCards.on("click", function () {
        const $form = $(this).closest("form"),
            $cards = $form.length ? $form.find(".payment-logo-card") : $paymentCards,
            $methodInput = $form.find("input[name='selected_method']").first(),
            $submitButton = $form.find("#payButton").first();

        $cards.removeClass("active selected").attr("aria-pressed", "false");
        $(this).addClass("active selected").attr("aria-pressed", "true");

        if ($methodInput.length) {
            $methodInput.val($(this).data("method") || $(this).data("payment-link-method"));
        } else {
            $selectedMethodInput.val($(this).data("method"));
        }

        if ($submitButton.length) {
            $submitButton.prop("disabled", false);
        } else {
            $payButton.prop("disabled", false);
        }
    });

    $paymentCards.on("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            $(this).trigger("click");
        }
    });

    $(".wallet-pin-input").on("input", function () {
        this.value = this.value.replace(/\D+/g, "").slice(0, 6);
    });
});
