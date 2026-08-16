/* =============================================================
 | DigiKash PWA registration
 | Registers the service worker and exposes a small install API.
 ============================================================= */
(function () {
    "use strict";

    var isLocalhost = ["localhost", "127.0.0.1"].indexOf(window.location.hostname) !== -1;
    var canUseServiceWorker = "serviceWorker" in navigator && (window.isSecureContext || isLocalhost);
    var isInsecureContext = !window.isSecureContext && !isLocalhost;

    var DISMISS_KEY = "dk_pwa_install_dismissed_until";
    var DISMISS_MS = 24 * 60 * 60 * 1000;
    var SERVICE_WORKER_SCOPE = "/";
    var FALLBACK_PROMPT_DELAY_MS = 3500;
    var INSTALL_BRIDGE_URL = "/launch?install=1";

    var deferredInstallPrompt = null;
    var registration = null;

    function isStandalone() {
        return window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;
    }

    function isMobileViewport() {
        return window.matchMedia("(max-width: 991.98px)").matches;
    }

    function isUserAppPage() {
        return window.location.pathname.indexOf("/user/") === 0 || window.location.pathname === "/user";
    }

    function isIosBrowser() {
        return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    }

    function shouldUseInstallBridge() {
        return isUserAppPage() && !isIosBrowser();
    }

    function currentReturnUrl() {
        return window.location.pathname + window.location.search + window.location.hash;
    }

    function installBridgeUrl() {
        var separator = INSTALL_BRIDGE_URL.indexOf("?") === -1 ? "?" : "&";

        return INSTALL_BRIDGE_URL + separator + "return=" + encodeURIComponent(currentReturnUrl());
    }

    function openInstallBridge() {
        try {
            window.location.assign(installBridgeUrl());
        } catch (error) {
            window.location.replace(installBridgeUrl());
        }
    }

    function isDismissed() {
        try {
            return Number(localStorage.getItem(DISMISS_KEY) || 0) > Date.now();
        } catch (error) {
            return false;
        }
    }

    function dismissPrompt() {
        try {
            localStorage.setItem(DISMISS_KEY, String(Date.now() + DISMISS_MS));
        } catch (error) {}

        hideInstallPrompt();
    }

    function setInstallButtonsVisible(visible) {
        document.querySelectorAll("[data-dk-pwa-install]").forEach(function (button) {
            if (button.closest("[data-dk-pwa-prompt]")) {
                button.hidden = false;

                return;
            }

            if (button.hasAttribute("data-dk-pwa-install-persistent")) {
                button.hidden = isStandalone();

                return;
            }

            button.hidden = !visible || isStandalone();
        });
    }

    function getInstallPrompt() {
        return document.querySelector("[data-dk-pwa-prompt]");
    }

    function setPromptText(message) {
        var text = document.querySelector("[data-dk-pwa-prompt-text]");
        if (text && message) {
            text.textContent = message;
        }
    }

    function setText(selector, message) {
        var element = document.querySelector(selector);
        if (element && message) {
            element.textContent = message;
        }
    }

    function browserInstallGuide() {
        var userAgent = window.navigator.userAgent;
        var isiOS = /iphone|ipad|ipod/i.test(userAgent);
        var isFirefox = /firefox|fxios/i.test(userAgent);
        var isSamsung = /samsungbrowser/i.test(userAgent);
        var isEdge = /edga|edgios|edg\//i.test(userAgent);
        var isOpera = /opr\/|opera|opt\//i.test(userAgent);
        var isChrome = /chrome|crios|chromium/i.test(userAgent) && !isSamsung && !isEdge && !isOpera;

        if (isiOS) {
            return {
                prompt: "Tap Share, then choose Add to Home Screen.",
                note: "iPhone and iPad browsers do not allow a website button to open the native install dialog directly.",
                stepOne: "Open this page in Safari and tap the Share button.",
                stepTwo: "Tap Add to Home Screen.",
                stepThree: "Tap Add to place the app icon on your home screen.",
            };
        }

        if (isSamsung) {
            return {
                prompt: "Tap Install. If the dialog does not open, use Samsung Internet menu and choose Add page to.",
                note: "Samsung Internet can install supported web apps from its browser menu when a direct install dialog is not available.",
                stepOne: "Tap the menu button in Samsung Internet.",
                stepTwo: "Tap Add page to, then choose Home screen or Apps screen.",
                stepThree: "Tap Add or Install to place the app icon on your device.",
            };
        }

        if (isEdge) {
            return {
                prompt: "Tap Install. If the dialog does not open, use Edge menu and choose Add to phone.",
                note: "Microsoft Edge can install supported web apps from its browser menu when a direct install dialog is not available.",
                stepOne: "Tap the three-dot menu in Microsoft Edge.",
                stepTwo: "Tap Add to phone or Add to Home screen.",
                stepThree: "Tap Add or Install to place the app icon on your home screen.",
            };
        }

        if (isOpera) {
            return {
                prompt: "Tap Install. If the dialog does not open, use Opera menu and choose Home screen.",
                note: "Opera can add supported web apps from its browser menu when a direct install dialog is not available.",
                stepOne: "Tap the menu button in Opera.",
                stepTwo: "Tap Add to Home screen.",
                stepThree: "Tap Add or Install to place the app icon on your home screen.",
            };
        }

        if (isFirefox) {
            return {
                prompt: "Tap Install. If the dialog does not open, use Firefox menu and choose Add to Home screen.",
                note: "Firefox may not expose the same native install dialog as Chromium browsers, but it can add supported sites from the browser menu.",
                stepOne: "Tap the menu button in Firefox.",
                stepTwo: "Tap Add to Home screen or Install.",
                stepThree: "Tap Add to place the app icon on your home screen.",
            };
        }

        if (isChrome) {
            return {
                prompt: "Tap Install. If the dialog does not open, use Chrome menu and choose Add to Home screen.",
                note: "Chrome opens the native install dialog only when the site passes browser install checks.",
                stepOne: "Tap the three-dot menu in the top-right corner of Chrome.",
                stepTwo: "Tap Install app or Add to Home screen.",
                stepThree: "Tap Add or Install to place the app icon on your home screen.",
            };
        }

        return {
            prompt: "Tap Install. If the dialog does not open, use your browser menu and choose Add to Home screen.",
            note: "This browser may not expose a direct install dialog, but it may still support adding the app from its menu.",
            stepOne: "Tap your browser menu button.",
            stepTwo: "Tap Install app or Add to Home screen.",
            stepThree: "Tap Add or Install to place the app icon on your home screen.",
        };
    }

    function updateInstallHelp() {
        var guide = browserInstallGuide();

        setText("[data-dk-pwa-help-note]", guide.note);
        setText("[data-dk-pwa-help-step-one]", guide.stepOne);
        setText("[data-dk-pwa-help-step-two]", guide.stepTwo);
        setText("[data-dk-pwa-help-step-three]", guide.stepThree);
    }

    function getHelpSheet() {
        return document.querySelector("[data-dk-pwa-help]");
    }

    function getHelpBackdrop() {
        return document.querySelector(".dk-pwa-help-backdrop");
    }

    function openInstallHelp() {
        var sheet = getHelpSheet();
        var backdrop = getHelpBackdrop();
        if (!sheet) {
            return;
        }

        if (window.DKMobile && typeof window.DKMobile.closeAll === "function") {
            window.DKMobile.closeAll();
        }

        sheet.classList.add("is-open");
        sheet.setAttribute("aria-hidden", "false");
        if (backdrop) {
            backdrop.classList.add("is-open");
        }
        document.body.classList.add("dk-scroll-lock");
    }

    function closeInstallHelp() {
        var sheet = getHelpSheet();
        var backdrop = getHelpBackdrop();
        if (sheet) {
            sheet.classList.remove("is-open");
            sheet.setAttribute("aria-hidden", "true");
        }
        if (backdrop) {
            backdrop.classList.remove("is-open");
        }
        document.body.classList.remove("dk-scroll-lock");
    }

    function showInstallPrompt() {
        var prompt = getInstallPrompt();
        if (!prompt || !isUserAppPage() || isStandalone() || !isMobileViewport() || isDismissed()) {
            return;
        }

        setInstallButtonsVisible(Boolean(deferredInstallPrompt));
        prompt.hidden = false;
        window.requestAnimationFrame(function () {
            prompt.classList.add("is-open");
        });
    }

    function manualInstallMessage() {
        return browserInstallGuide().prompt;
    }

    function fallbackPromptMessage() {
        if (shouldUseInstallBridge()) {
            return "Tap Install to open the secure app install prompt.";
        }

        return manualInstallMessage();
    }

    function showFallbackInstallPrompt() {
        if (deferredInstallPrompt || isStandalone()) {
            return;
        }

        setPromptText(fallbackPromptMessage());
        showInstallPrompt();
    }

    function hideInstallPrompt() {
        var prompt = getInstallPrompt();
        if (!prompt) {
            return;
        }

        prompt.classList.remove("is-open");
        window.setTimeout(function () {
            prompt.hidden = true;
        }, 260);
    }

    function maybeShowIosPrompt() {
        var isiOS = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(window.navigator.userAgent);
        if (!isiOS || !isSafari || isStandalone() || !isMobileViewport() || isDismissed()) {
            return;
        }

        var text = document.querySelector("[data-dk-pwa-prompt-text]");
        if (text) {
            text.textContent = browserInstallGuide().prompt;
        }

        window.setTimeout(showInstallPrompt, 900);
    }


    window.addEventListener("beforeinstallprompt", function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;
        setInstallButtonsVisible(true);
        setPromptText(null);

        window.setTimeout(showInstallPrompt, 600);
        window.dispatchEvent(new CustomEvent("dk:pwa-install-available"));
    });

    window.addEventListener("appinstalled", function () {
        deferredInstallPrompt = null;
        setInstallButtonsVisible(false);
        hideInstallPrompt();
        window.dispatchEvent(new CustomEvent("dk:pwa-installed"));
    });

    document.addEventListener("click", function (event) {
        var target = event.target instanceof Element ? event.target : event.target.parentElement;
        var installButton = target ? target.closest("[data-dk-pwa-install]") : null;
        if (!installButton) {
            return;
        }

        event.preventDefault();
        window.DKPwa.install();
    });

    document.addEventListener("click", function (event) {
        var target = event.target instanceof Element ? event.target : event.target.parentElement;
        var dismissButton = target ? target.closest("[data-dk-pwa-dismiss]") : null;
        if (!dismissButton) {
            return;
        }

        event.preventDefault();
        dismissPrompt();
    });

    document.addEventListener("click", function (event) {
        var target = event.target instanceof Element ? event.target : event.target.parentElement;
        var closeButton = target ? target.closest("[data-dk-pwa-help-close]") : null;
        if (!closeButton) {
            return;
        }

        event.preventDefault();
        closeInstallHelp();
    });

    window.addEventListener("load", function () {
        setInstallButtonsVisible(Boolean(deferredInstallPrompt));
        maybeShowIosPrompt();

        window.setTimeout(showFallbackInstallPrompt, FALLBACK_PROMPT_DELAY_MS);

        if (isInsecureContext && window.console && window.console.warn) {
            window.console.warn("[DigiKash PWA] Install prompt requires HTTPS.");
        }

        if (!canUseServiceWorker) {
            return;
        }

        navigator.serviceWorker.getRegistrations()
            .then(function (registrations) {
                registrations.forEach(function (existingRegistration) {
                    if (existingRegistration.scope !== window.location.origin + SERVICE_WORKER_SCOPE) {
                        existingRegistration.unregister();
                    }
                });
            })
            .catch(function () {});

        navigator.serviceWorker.register("/service-worker.js", { scope: SERVICE_WORKER_SCOPE })
            .then(function (serviceWorkerRegistration) {
                registration = serviceWorkerRegistration;

                registration.addEventListener("updatefound", function () {
                    var installingWorker = registration.installing;
                    if (!installingWorker) {
                        return;
                    }

                    installingWorker.addEventListener("statechange", function () {
                        if (installingWorker.state === "installed" && navigator.serviceWorker.controller) {
                            window.dispatchEvent(new CustomEvent("dk:pwa-update-ready"));
                        }
                    });
                });

                setInterval(function () {
                    registration.update().catch(function () {});
                }, 60 * 60 * 1000);
            })
            .catch(function (error) {
                if (window.console && window.console.warn) {
                    window.console.warn("[DigiKash PWA] Service worker registration failed:", error);
                }
            });
    });

    if (canUseServiceWorker) {
        navigator.serviceWorker.addEventListener("controllerchange", function () {
            window.dispatchEvent(new CustomEvent("dk:pwa-controller-change"));
        });
    }

    window.DKPwa = {
        canInstall: function () {
            return Boolean(deferredInstallPrompt);
        },
        install: function () {
            if (!deferredInstallPrompt) {
                if (!isStandalone()) {
                    if (shouldUseInstallBridge()) {
                        setPromptText("Opening the secure app install prompt...");
                        showInstallPrompt();
                        openInstallBridge();

                        return Promise.resolve(false);
                    }

                    setPromptText(manualInstallMessage());
                    showInstallPrompt();
                    updateInstallHelp();
                    openInstallHelp();
                }

                return Promise.resolve(false);
            }

            var promptEvent = deferredInstallPrompt;
            var promptResult;

            try {
                promptResult = promptEvent.prompt();
            } catch (error) {
                return Promise.resolve(false);
            }

            var resolvedChoice = (promptResult && typeof promptResult.then === "function")
                ? promptResult
                : promptEvent.userChoice;

            return Promise.resolve(resolvedChoice).then(function (choice) {
                deferredInstallPrompt = null;
                hideInstallPrompt();
                setInstallButtonsVisible(false);

                return choice && choice.outcome === "accepted";
            }).catch(function () {
                deferredInstallPrompt = null;

                return false;
            });
        },
        activateUpdate: function () {
            if (registration && registration.waiting) {
                registration.waiting.postMessage({ type: "SKIP_WAITING" });
            }
        },
    };
})();
