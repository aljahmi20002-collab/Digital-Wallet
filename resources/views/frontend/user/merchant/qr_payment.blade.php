{{--
    DEPRECATED — file intentionally left empty.

    The merchant QR payment screen was removed when the QR / payment-link
    flow was merged into the unified Payment Link module. Merchants now
    create per-shop payment links from:
        user.payment-links.create?merchant_id={id}

    Routes user.merchant.qr-payment / qr-generate / qr-history were removed
    from routes/web.php and the corresponding controller methods were
    removed from MerchantController. This stub exists only because the
    sandbox does not allow deleting files; it is unreferenced by any
    route, controller, view or test, and can be safely deleted on disk.
--}}
