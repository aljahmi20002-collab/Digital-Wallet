"use strict";

(() => {
    const config = window.DigiKashLandingBridge || {};
    const actions = config.actions || {};

    const resolveAction = (element) => element.getAttribute("data-dk-action") || element.getAttribute("data-dk-route");

    const csrfToken = () => {
        const token = document.querySelector('meta[name="csrf-token"]');

        return token ? token.getAttribute("content") : "";
    };

    const submitAction = (action) => {
        const method = (action.method || "GET").toUpperCase();

        if (method === "GET") {
            window.location.href = action.url;

            return;
        }

        const form = document.createElement("form");
        form.method = "POST";
        form.action = action.url;
        form.hidden = true;

        const methodInput = document.createElement("input");
        methodInput.type = "hidden";
        methodInput.name = "_method";
        methodInput.value = method;
        form.appendChild(methodInput);

        const token = csrfToken();

        if (token) {
            const tokenInput = document.createElement("input");
            tokenInput.type = "hidden";
            tokenInput.name = "_token";
            tokenInput.value = token;
            form.appendChild(tokenInput);
        }

        document.body.appendChild(form);
        form.submit();
    };

    const navigate = (key, target) => {
        const action = actions[key];

        if (!action || !action.url) {
            return false;
        }

        if ((action.method || "GET").toUpperCase() === "GET" && target === "_blank") {
            window.open(action.url, "_blank", "noopener,noreferrer");

            return true;
        }

        submitAction(action);

        return true;
    };

    const hydrateAction = (element) => {
        const key = resolveAction(element);
        const action = actions[key];

        if (!key || !action || !action.url) {
            element.setAttribute("data-dk-action-missing", key || "unknown");

            return;
        }

        if (element.tagName === "A" && (!element.getAttribute("href") || element.getAttribute("href") === "#")) {
            element.setAttribute("href", action.url);
        }

        element.addEventListener("click", (event) => {
            const method = (action.method || "GET").toUpperCase();

            if (element.tagName === "A" && method === "GET" && element.getAttribute("href") === action.url) {
                return;
            }

            event.preventDefault();
            navigate(key, element.getAttribute("target"));
        });
    };

    const boot = () => {
        document.querySelectorAll("[data-dk-action], [data-dk-route]").forEach(hydrateAction);
        document.documentElement.classList.add("dk-landing-bridge-ready");
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", boot, { once: true });
    } else {
        boot();
    }

    window.DigiKashLanding = {
        actions,
        navigate,
    };
})();
