"use strict";

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { decodeEntities } = window.wp.htmlEntities;
const { createElement, Fragment } = window.wp.element;

const settings = getSetting("digikash_data", {});
const label = decodeEntities(settings.title || "Secure Payment");
const brandName = settings.brand_name || label;
const checkoutHeading = settings.checkout_heading || "Hosted payment page";
const checkoutNotice = settings.checkout_notice || settings.description || "You will be redirected to a secure hosted checkout to complete the order.";
const securityBadgeText = settings.security_badge_text || "Secure";
const siteLogo = settings.site_logo || "";
const featureBadges = Array.isArray(settings.feature_badges) ? settings.feature_badges.filter(Boolean) : [];
const sandboxMessage = settings.sandbox_message || "Sandbox mode is active. No real payment will be collected.";

const styles = {
    container: {
        border: "1px solid #d8dde6",
        borderRadius: "16px",
        background: "linear-gradient(180deg, #ffffff 0%, #f7f9fc 100%)",
        padding: "18px",
        boxShadow: "0 10px 28px rgba(15, 23, 42, 0.06)",
    },
    header: {
        display: "flex",
        justifyContent: "space-between",
        alignItems: "flex-start",
        gap: "12px",
        marginBottom: "12px",
    },
    brand: {
        display: "flex",
        alignItems: "center",
        gap: "12px",
        minWidth: 0,
    },
    logoWrap: {
        width: "40px",
        height: "40px",
        borderRadius: "12px",
        background: "#ffffff",
        border: "1px solid #e5e7eb",
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        overflow: "hidden",
        flexShrink: 0,
    },
    logo: {
        width: "100%",
        height: "100%",
        objectFit: "contain",
    },
    title: {
        display: "block",
        margin: 0,
        fontSize: "15px",
        fontWeight: "700",
        color: "#111827",
        lineHeight: "1.35",
    },
    subtitle: {
        marginTop: "3px",
        fontSize: "12px",
        color: "#6b7280",
    },
    badge: {
        display: "inline-flex",
        alignItems: "center",
        padding: "6px 10px",
        borderRadius: "999px",
        background: "#ecfdf3",
        color: "#027a48",
        border: "1px solid #abefc6",
        fontSize: "11px",
        fontWeight: "700",
        whiteSpace: "nowrap",
    },
    description: {
        margin: "0 0 12px",
        color: "#4b5563",
        fontSize: "13px",
        lineHeight: "1.7",
    },
    featureWrap: {
        display: "flex",
        flexWrap: "wrap",
        gap: "8px",
    },
    feature: {
        display: "inline-flex",
        alignItems: "center",
        padding: "7px 10px",
        borderRadius: "999px",
        background: "#ffffff",
        border: "1px solid #e5e7eb",
        color: "#374151",
        fontSize: "11px",
        fontWeight: "600",
    },
    alert: {
        marginTop: "14px",
        padding: "12px 14px",
        borderRadius: "12px",
        background: "#fffbeb",
        border: "1px solid #fde68a",
        color: "#92400e",
        fontSize: "12px",
        lineHeight: "1.6",
    },
};

const Content = () => createElement("div", { style: styles.container }, [
    createElement("div", { key: "header", style: styles.header }, [
        createElement("div", { key: "brand", style: styles.brand }, [
            siteLogo
                ? createElement("span", { key: "logo-wrap", style: styles.logoWrap }, [
                    createElement("img", {
                        key: "logo",
                        src: siteLogo,
                        alt: brandName,
                        style: styles.logo,
                    }),
                ])
                : null,
            createElement("div", { key: "brand-content" }, [
                createElement("span", { key: "brand-name", style: styles.title }, brandName),
                createElement("div", { key: "heading", style: styles.subtitle }, checkoutHeading),
            ]),
        ]),
        createElement("span", { key: "badge", style: styles.badge }, securityBadgeText),
    ]),
    createElement("p", { key: "description", style: styles.description }, checkoutNotice),
    featureBadges.length
        ? createElement("div", { key: "features", style: styles.featureWrap },
            featureBadges.map((feature, index) => createElement("span", {
                key: `feature-${index}`,
                style: styles.feature,
            }, feature))
        )
        : null,
    settings.testmode
        ? createElement("div", { key: "sandbox", style: styles.alert }, sandboxMessage)
        : null,
]);

const Label = ({ components }) => {
    const { PaymentMethodLabel } = components;

    if (! siteLogo) {
        return createElement(PaymentMethodLabel, { text: label });
    }

    return createElement(Fragment, null, createElement("div", {
        style: {
            display: "flex",
            alignItems: "center",
            gap: "8px",
            fontSize: "14px",
            fontWeight: "600",
        },
    }, [
        createElement("img", {
            key: "label-logo",
            src: siteLogo,
            alt: brandName,
            style: {
                width: "20px",
                height: "20px",
                borderRadius: "6px",
                objectFit: "contain",
            },
        }),
        createElement("span", { key: "label-text" }, label),
    ]));
};

registerPaymentMethod({
    name: "digikash",
    label: createElement(Label),
    content: createElement(Content),
    edit: createElement(Content),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports || ["products"],
    },
});
