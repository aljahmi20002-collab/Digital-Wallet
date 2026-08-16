/* =============================================================
 | DigiKash — Mobile Dashboard JS controller
 | Active only on viewports < 992px (matchMedia guard).
 | Handles: sticky-header scroll state, scroll-aware tab bar,
 | balance hide, sheets
 | (More / Language / Apps / Notifications), wallet carousel
 | dots, segmented insight switcher.
 ============================================================= */
(function () {
    "use strict";

    var mobileMq = window.matchMedia("(max-width: 991.98px)");

    /* ---------- Body scroll lock ---------- */
    var lockCount = 0;
    function lockBody() {
        lockCount++;
        document.body.classList.add("dk-scroll-lock");
    }
    function unlockBody() {
        lockCount = Math.max(0, lockCount - 1);
        if (lockCount === 0) document.body.classList.remove("dk-scroll-lock");
    }

    /* ---------- Sheet / panel registry ---------- */
    var openables = [];
    function registerOpenable(opener) {
        openables.push(opener);
    }
    function closeAll() {
        openables.forEach(function (o) { try { o.close(); } catch (e) {} });
    }

    /* ---------- Sheet helper (works for bottom sheet + side panel) ---------- */
    function bindSheet(triggerSelector, panelSelector, backdropSelector, closeSelector) {
        var panel = document.querySelector(panelSelector);
        var backdrop = backdropSelector ? document.querySelector(backdropSelector) : null;
        if (!panel) return null;
        if (panel.__dkSheetApi) return panel.__dkSheetApi;

        function triggers() {
            return document.querySelectorAll(triggerSelector);
        }

        function open() {
            // close any others first
            openables.forEach(function (o) { if (o.panel !== panel) o.close(); });
            panel.classList.add("is-open");
            if (backdrop) backdrop.classList.add("is-open");
            panel.setAttribute("aria-hidden", "false");
            lockBody();
            triggers().forEach(function (t) { t.setAttribute("aria-expanded", "true"); });
        }
        function close() {
            if (!panel.classList.contains("is-open")) return;
            panel.classList.remove("is-open");
            if (backdrop) backdrop.classList.remove("is-open");
            panel.setAttribute("aria-hidden", "true");
            unlockBody();
            triggers().forEach(function (t) { t.setAttribute("aria-expanded", "false"); });
        }

        document.addEventListener("click", function (e) {
            var trigger = e.target.closest(triggerSelector);
            if (!trigger) return;

            e.preventDefault();
            e.stopPropagation();

            if (panel.classList.contains("is-open")) close();
            else open();
        });

        if (backdrop) backdrop.addEventListener("click", close);
        if (closeSelector) {
            panel.querySelectorAll(closeSelector).forEach(function (b) {
                b.addEventListener("click", close);
            });
        }
        var api = { open: open, close: close, panel: panel };
        panel.__dkSheetApi = api;
        registerOpenable(api);
        return api;
    }

    /* ---------- Sticky header scroll state + scroll-aware tab bar ---------- */
    function initScrollBehavior() {
        var header = document.querySelector(".dk-mobile-header");
        var tabbar = document.querySelector(".dk-tabbar");
        var ticking = false;
        function update() {
            var y = window.scrollY || window.pageYOffset || 0;
            if (header) header.setAttribute("data-scrolled", y > 8 ? "1" : "0");
            if (tabbar) {
                tabbar.setAttribute("data-hidden", "0");
            }
            ticking = false;
        }
        window.addEventListener("scroll", function () {
            if (!ticking) {
                window.requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });
        update();
    }

    /* ---------- Wallet carousel dots ---------- */
    function initWalletDots() {
        var track = document.querySelector(".dk-wallet-track");
        var dots = document.querySelectorAll(".dk-dots span");
        if (!track || !dots.length) return;
        function setActive() {
            var card = track.querySelector(".dk-wallet-card");
            if (!card) return;
            var step = card.offsetWidth + 12;
            var idx = Math.round(track.scrollLeft / step);
            idx = Math.max(0, Math.min(dots.length - 1, idx));
            dots.forEach(function (d, i) { d.setAttribute("data-active", i === idx ? "1" : "0"); });
        }
        track.addEventListener("scroll", function () { window.requestAnimationFrame(setActive); }, { passive: true });
        setActive();
    }

    /* ---------- Hide / show balance ---------- */
    function initBalanceToggle() {
        var stored = (function () { try { return localStorage.getItem("dk_hide_bal"); } catch (e) { return null; } })();
        var hidden = stored === "1";
        var balanceTargets = ".dk-wc__amount, [data-balance-mask]";
        function apply() {
            document.querySelectorAll(balanceTargets).forEach(function (el) {
                el.setAttribute("data-hidden", hidden ? "1" : "0");
            });
            document.querySelectorAll(".dk-balance-eye i").forEach(function (i) {
                i.className = hidden ? "fa fa-eye-slash" : "fa fa-eye";
            });
            document.querySelectorAll(".dk-balance-eye").forEach(function (btn) {
                btn.setAttribute("aria-pressed", hidden ? "true" : "false");
            });
        }
        function toggle(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            hidden = !hidden;
            try { localStorage.setItem("dk_hide_bal", hidden ? "1" : "0"); } catch (er) {}
            apply();
        }
        document.querySelectorAll(".dk-balance-eye").forEach(function (btn) {
            if (btn.__dkBalanceToggleBound) return;
            btn.__dkBalanceToggleBound = true;
            btn.addEventListener("click", toggle);
            btn.addEventListener("keydown", function (e) {
                if (e.key !== "Enter" && e.key !== " ") return;
                toggle(e);
            });
        });
        apply();
    }

    /* ---------- Insight segmented control ---------- */
    function initInsightSegments() {
        document.querySelectorAll(".dk-seg").forEach(function (seg) {
            var btns = seg.querySelectorAll(".dk-seg__btn");
            btns.forEach(function (b) {
                b.addEventListener("click", function () {
                    btns.forEach(function (x) {
                        x.setAttribute("data-active", "0");
                        x.setAttribute("aria-selected", "false");
                    });
                    b.setAttribute("data-active", "1");
                    b.setAttribute("aria-selected", "true");
                    var mode = b.getAttribute("data-mode");
                    var insight = seg.closest(".dk-insight");
                    if (!insight) return;
                    insight.setAttribute("data-mode", mode);
                    // Toggle big-number panels
                    insight.querySelectorAll("[data-big]").forEach(function (p) {
                        p.style.display = p.getAttribute("data-big") === mode ? "" : "none";
                    });
                    // Notify chart
                    try {
                        window.dispatchEvent(new CustomEvent("dk:insight-mode", { detail: { mode: mode } }));
                    } catch (e) {}
                });
            });
        });
    }

    function notify(type, message) {
        if (window.notifyEvs && message) {
            window.notifyEvs(type, message);
        }
    }

    function copyTextToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var ta = document.createElement("textarea");
            ta.value = text;
            ta.setAttribute("readonly", "readonly");
            ta.style.position = "fixed";
            ta.style.top = "-1000px";
            document.body.appendChild(ta);
            ta.select();

            try {
                var ok = document.execCommand("copy");
                document.body.removeChild(ta);
                if (ok) resolve();
                else reject(new Error("Copy command failed"));
            } catch (error) {
                document.body.removeChild(ta);
                reject(error);
            }
        });
    }

    function markWalletQrButton(button) {
        var label = button.querySelector("[data-wallet-qr-action-label]");
        var successLabel = button.getAttribute("data-wallet-qr-success-label");

        if (label && successLabel && !button.getAttribute("data-wallet-qr-default-label")) {
            button.setAttribute("data-wallet-qr-default-label", label.textContent);
        }

        if (label && successLabel) {
            label.textContent = successLabel;
        }

        button.classList.add("is-done");

        setTimeout(function () {
            if (label && successLabel) {
                label.textContent = button.getAttribute("data-wallet-qr-default-label");
            }
            button.classList.remove("is-done");
        }, 1200);
    }

    function walletQrSvg(button) {
        var card = button.closest("[data-wallet-qr-card]");
        if (!card) return null;

        return card.querySelector("[data-wallet-qr-svg] svg");
    }

    function downloadWalletQr(button) {
        var svg = walletQrSvg(button);
        if (!svg) return;

        var source = new XMLSerializer().serializeToString(svg);
        if (source.indexOf("xmlns=") === -1) {
            source = source.replace("<svg", '<svg xmlns="http://www.w3.org/2000/svg"');
        }

        var blob = new Blob([source], { type: "image/svg+xml;charset=utf-8" });
        var url = window.URL.createObjectURL(blob);
        var link = document.createElement("a");

        link.href = url;
        link.download = button.getAttribute("data-wallet-qr-filename") || "digikash-wallet-qr.svg";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        markWalletQrButton(button);
        notify("success", button.getAttribute("data-toast-message") || "QR code downloaded");
    }

    function initWalletQrActions() {
        if (document.documentElement.getAttribute("data-wallet-qr-actions-ready") === "1") return;
        document.documentElement.setAttribute("data-wallet-qr-actions-ready", "1");

        document.addEventListener("click", function (event) {
            var copyButton = event.target.closest("[data-wallet-qr-copy]");
            var shareButton = event.target.closest("[data-wallet-qr-share]");
            var downloadButton = event.target.closest("[data-wallet-qr-download]");

            if (copyButton) {
                event.preventDefault();
                copyTextToClipboard(copyButton.getAttribute("data-wallet-qr-link") || "").then(function () {
                    markWalletQrButton(copyButton);
                    notify("success", copyButton.getAttribute("data-toast-message") || "Receive link copied");
                }).catch(function () {
                    notify("error", "Could not copy. Please copy the link manually.");
                });
                return;
            }

            if (shareButton) {
                event.preventDefault();

                var shareUrl = shareButton.getAttribute("data-wallet-qr-link") || "";
                var shareData = {
                    title: shareButton.getAttribute("data-wallet-qr-title") || document.title,
                    text: shareButton.getAttribute("data-wallet-qr-text") || "",
                    url: shareUrl,
                };

                if (navigator.share) {
                    navigator.share(shareData).then(function () {
                        markWalletQrButton(shareButton);
                    }).catch(function () {});
                    return;
                }

                copyTextToClipboard(shareUrl).then(function () {
                    markWalletQrButton(shareButton);
                    notify("success", shareButton.getAttribute("data-toast-message") || "Receive link copied");
                }).catch(function () {
                    notify("error", "Could not share. Please copy the link manually.");
                });
                return;
            }

            if (downloadButton) {
                event.preventDefault();
                downloadWalletQr(downloadButton);
            }
        });

        document.querySelectorAll("[data-wallet-qr-link-input]").forEach(function (input) {
            input.addEventListener("focus", function () { input.select(); });
            input.addEventListener("click", function () { input.select(); });
        });
    }

    /* ---------- Copy buttons ---------- */
    function initCopy() {
        document.querySelectorAll("[data-dk-copy]").forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                var text = btn.getAttribute("data-dk-copy");
                if (!text) return;
                copyTextToClipboard(text).then(function () {
                    btn.classList.add("is-copied");
                    setTimeout(function () { btn.classList.remove("is-copied"); }, 800);
                }).catch(function () {});
            });
        });
    }

    /* ---------- KYC dismiss ---------- */
    function initKycDismiss() {
        document.querySelectorAll(".dk-kyc__close").forEach(function (b) {
            b.addEventListener("click", function () {
                var card = b.closest(".dk-kyc");
                if (card) card.style.display = "none";
            });
        });
    }

    /* ---------- Esc closes panels ---------- */
    function initEsc() {
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") closeAll();
        });
    }

    /* ---------- Payment QR scanner ---------- */
    function initQrScanner() {
        var modal = document.querySelector("[data-dk-qr-scanner-modal]");
        var backdrop = document.querySelector("[data-dk-qr-scanner-backdrop]");
        if (!modal || modal.getAttribute("data-ready") === "1") return;

        modal.setAttribute("data-ready", "1");

        var video = modal.querySelector("[data-dk-qr-scanner-video]");
        var canvas = modal.querySelector("[data-dk-qr-scanner-canvas]");
        var status = modal.querySelector("[data-dk-qr-scanner-status]");
        var input = modal.querySelector("[data-dk-qr-scanner-input]");
        var manualForm = modal.querySelector("[data-dk-qr-scanner-manual]");
        var permissionButton = modal.querySelector("[data-dk-qr-scanner-permission]");
        var closeButtons = modal.querySelectorAll("[data-dk-qr-scanner-close]");
        var stream = null;
        var detector = null;
        var frameRequest = null;
        var scanning = false;
        var lastScanAt = 0;

        function scannerButtons() {
            return document.querySelectorAll("[data-dk-qr-scanner-open]");
        }

        function setScannerButtonsExpanded(isExpanded) {
            scannerButtons().forEach(function (button) {
                button.setAttribute("aria-expanded", isExpanded ? "true" : "false");
            });
        }

        function message(key, fallback) {
            return modal.getAttribute("data-" + key + "-label") || fallback;
        }

        function setStatus(text) {
            if (status) status.textContent = text;
        }

        function showPermissionButton() {
            if (permissionButton) permissionButton.hidden = false;
        }

        function hidePermissionButton() {
            if (permissionButton) permissionButton.hidden = true;
        }

        function isPermissionError(error) {
            return error && ["NotAllowedError", "PermissionDeniedError", "SecurityError"].indexOf(error.name) !== -1;
        }

        function escapeRegExp(value) {
            return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        }

        function configuredPaymentLinkBase() {
            var fallback = window.location.origin + "/payment-link";
            var rawBase = modal.getAttribute("data-payment-link-base-url") || fallback;

            try {
                return new URL(rawBase, window.location.origin);
            } catch (error) {
                return new URL(fallback);
            }
        }

        function configuredSendMoneyBase() {
            var fallback = window.location.origin + "/user/send-money/create";
            var rawBase = modal.getAttribute("data-send-money-base-url") || fallback;

            try {
                return new URL(rawBase, window.location.origin);
            } catch (error) {
                return new URL(fallback);
            }
        }

        function configuredAgentCashOutUrlTemplate() {
            var fallback = window.location.origin + "/user/agent/qr/__TOKEN__/cash-out";
            var rawTemplate = modal.getAttribute("data-agent-cash-out-url-template") || fallback;

            try {
                return new URL(rawTemplate, window.location.origin);
            } catch (error) {
                return new URL(fallback);
            }
        }

        function looksLikeWalletId(value) {
            return /^DK[-A-Z0-9]+$/i.test(value)
                || /^[0-9]{6,}$/.test(value)
                || /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(value);
        }

        function paymentLinkUrl(value) {
            var raw = (value || "").trim();
            if (!raw) return null;

            if (/^[a-z0-9.-]+\.[a-z]{2,}(\/|$)/i.test(raw)) {
                raw = "https://" + raw;
            }

            try {
                var base = configuredPaymentLinkBase();
                var pathPrefix = base.pathname.replace(/\/+$/, "") || "/payment-link";
                var allowedOrigins = [window.location.origin];
                if (allowedOrigins.indexOf(base.origin) === -1) allowedOrigins.push(base.origin);
                var url = new URL(raw, window.location.origin);
                var path = url.pathname.replace(/\/+$/, "");
                var routePattern = new RegExp("^" + escapeRegExp(pathPrefix) + "/[A-Za-z0-9_-]+$");

                if (allowedOrigins.indexOf(url.origin) === -1) return null;
                if (!routePattern.test(path)) return null;

                return url;
            } catch (error) {
                return null;
            }
        }

        function sendMoneyUrl(value) {
            var raw = (value || "").trim();
            if (!raw) return null;

            var base = configuredSendMoneyBase();

            if (looksLikeWalletId(raw)) {
                var walletUrl = new URL(base.href);
                walletUrl.searchParams.set("recipient", raw);

                return walletUrl;
            }

            if (/^[a-z0-9.-]+\.[a-z]{2,}(\/|$)/i.test(raw)) {
                raw = "https://" + raw;
            }

            try {
                var allowedOrigins = [window.location.origin];
                if (allowedOrigins.indexOf(base.origin) === -1) allowedOrigins.push(base.origin);

                var url = new URL(raw, window.location.origin);
                var basePath = base.pathname.replace(/\/+$/, "");
                var path = url.pathname.replace(/\/+$/, "");
                var recipient = (url.searchParams.get("recipient") || "").trim();

                if (allowedOrigins.indexOf(url.origin) === -1) return null;
                if (path !== basePath) return null;
                if (!recipient) return null;

                return url;
            } catch (error) {
                return null;
            }
        }

        function agentCashOutUrl(value) {
            var raw = (value || "").trim();
            if (!raw) return null;

            var template = configuredAgentCashOutUrlTemplate();
            var tokenPlaceholder = "__TOKEN__";

            if (/^aqr_[A-Za-z0-9_-]+$/.test(raw)) {
                var tokenUrl = new URL(template.href);
                tokenUrl.pathname = tokenUrl.pathname.replace(tokenPlaceholder, raw);

                return tokenUrl;
            }

            if (/^[a-z0-9.-]+\.[a-z]{2,}(\/|$)/i.test(raw)) {
                raw = "https://" + raw;
            }

            try {
                var allowedOrigins = [window.location.origin];
                if (allowedOrigins.indexOf(template.origin) === -1) allowedOrigins.push(template.origin);

                var url = new URL(raw, window.location.origin);
                var templatePath = template.pathname.replace(/\/+$/, "");
                var path = url.pathname.replace(/\/+$/, "");
                var encodedPlaceholder = encodeURIComponent(tokenPlaceholder);
                var routePattern = new RegExp(
                    "^" + escapeRegExp(templatePath)
                        .replace(escapeRegExp(tokenPlaceholder), "[A-Za-z0-9_-]+")
                        .replace(escapeRegExp(encodedPlaceholder), "[A-Za-z0-9_-]+") + "$"
                );

                if (allowedOrigins.indexOf(url.origin) === -1) return null;
                if (!routePattern.test(path)) return null;

                return url;
            } catch (error) {
                return null;
            }
        }

        function paymentQrUrl(value) {
            return paymentLinkUrl(value) || sendMoneyUrl(value) || agentCashOutUrl(value);
        }

        function stopCamera() {
            scanning = false;
            if (frameRequest) {
                window.cancelAnimationFrame(frameRequest);
                frameRequest = null;
            }
            if (stream) {
                stream.getTracks().forEach(function (track) { track.stop(); });
                stream = null;
            }
            if (video) {
                video.pause();
                video.srcObject = null;
            }
        }

        function closeScanner() {
            if (!modal.classList.contains("is-open")) return;
            stopCamera();
            modal.classList.remove("is-open");
            modal.setAttribute("aria-hidden", "true");
            if (backdrop) backdrop.classList.remove("is-open");
            setScannerButtonsExpanded(false);
            unlockBody();
        }

        function openPayment(value) {
            var url = paymentQrUrl(value);
            if (!url) {
                setStatus(message("invalid", "This QR code is not a valid DigiKash payment, wallet, or agent QR."));
                return false;
            }

            setStatus(message("detected", "QR code detected. Opening..."));
            stopCamera();
            window.location.href = url.href;

            return true;
        }

        function scanFrame(now) {
            if (!scanning || !detector || !video) return;

            if (now - lastScanAt < 220 || video.readyState < 2) {
                frameRequest = window.requestAnimationFrame(scanFrame);
                return;
            }

            lastScanAt = now;
            detector.detect(video).then(function (codes) {
                if (codes && codes.length) {
                    var value = codes[0].rawValue || "";
                    if (openPayment(value)) return;
                }

                frameRequest = window.requestAnimationFrame(scanFrame);
            }).catch(function () {
                frameRequest = window.requestAnimationFrame(scanFrame);
            });
        }

        function requestCamera() {
            return navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: "environment" } },
                audio: false,
            }).catch(function () {
                return navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: false,
                });
            });
        }

        function createDetector() {
            if (!("BarcodeDetector" in window)) {
                return Promise.resolve(null);
            }

            return Promise.resolve(
                window.BarcodeDetector.getSupportedFormats
                    ? window.BarcodeDetector.getSupportedFormats()
                    : ["qr_code"]
            ).then(function (formats) {
                if (formats.length && formats.indexOf("qr_code") === -1) {
                    return null;
                }

                return new window.BarcodeDetector({ formats: ["qr_code"] });
            });
        }

        function startCamera() {
            stopCamera();
            hidePermissionButton();
            setStatus(message("starting", "Starting camera..."));

            if (!video || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus(message("camera-error", "Camera access is blocked or unavailable. Paste the QR link below."));
                showPermissionButton();
                return;
            }

            requestCamera().then(function (mediaStream) {
                if (!modal.classList.contains("is-open")) {
                    mediaStream.getTracks().forEach(function (track) { track.stop(); });
                    return Promise.reject(new Error("Scanner closed"));
                }

                stream = mediaStream;
                video.srcObject = mediaStream;

                return video.play();
            }).then(function () {
                return createDetector();
            }).then(function (barcodeDetector) {
                detector = barcodeDetector;

                if (!detector) {
                    if (canvas) canvas.hidden = true;
                    setStatus(message("unsupported", "Camera QR scanning is not supported on this browser. Paste the QR link below."));
                    return;
                }

                return null;
            }).then(function () {
                if (!modal.classList.contains("is-open")) {
                    stopCamera();
                    return;
                }

                if (!detector) {
                    return;
                }

                scanning = true;
                lastScanAt = 0;
                hidePermissionButton();
                setStatus(message("ready", "Point your camera at a payment link or wallet QR code."));
                frameRequest = window.requestAnimationFrame(scanFrame);
            }).catch(function (error) {
                if (error && error.message === "Scanner closed") return;
                showPermissionButton();
                setStatus(
                    isPermissionError(error)
                        ? message("camera-denied", "Camera permission is blocked. Enable camera access from browser site settings, then tap Allow Camera.")
                        : message("camera-permission", "Camera permission is required to scan QR codes. Tap Allow Camera and approve access.")
                );
            });
        }

        function openScanner() {
            if (modal.classList.contains("is-open")) return;
            closeAll();
            modal.classList.add("is-open");
            modal.setAttribute("aria-hidden", "false");
            if (backdrop) backdrop.classList.add("is-open");
            setScannerButtonsExpanded(true);
            lockBody();
            startCamera();
        }

        if (permissionButton) {
            permissionButton.addEventListener("click", function (event) {
                event.preventDefault();
                startCamera();
            });
        }

        document.addEventListener("click", function (event) {
            var button = event.target.closest("[data-dk-qr-scanner-open]");
            if (!button) return;

            event.preventDefault();
            openScanner();
        });

        closeButtons.forEach(function (button) {
            button.addEventListener("click", closeScanner);
        });

        if (backdrop) {
            backdrop.addEventListener("click", closeScanner);
        }

        if (manualForm) {
            manualForm.addEventListener("submit", function (event) {
                event.preventDefault();
                openPayment(input ? input.value : "");
            });
        }

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") closeScanner();
        });
    }

    /* ---------- Init only on mobile widths ---------- */
    function bootMobile() {
        if (!mobileMq.matches) return;
        bindSheet(".dk-open-more",    ".dk-more-sheet",    ".dk-more-backdrop",    ".dk-sheet-close");
        bindSheet(".dk-open-lang",    ".dk-lang-sheet",    ".dk-lang-backdrop",    ".dk-sheet-close");
        bindSheet(".dk-open-apps",    ".dk-apps-sheet",    ".dk-apps-backdrop",    ".dk-sheet-close");
        bindSheet(".dk-open-notif",   ".dk-notif-panel",   ".dk-notif-backdrop",   ".dk-sheet-close");
        initScrollBehavior();
        initWalletDots();
        initInsightSegments();
        initCopy();
        initKycDismiss();
        initEsc();
    }

    function bootShared() {
        initBalanceToggle();
        initQrScanner();
        initWalletQrActions();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            bootMobile();
            bootShared();
        });
    } else {
        bootMobile();
        bootShared();
    }

    // Also run on viewport change so resizing into mobile activates.
    if (mobileMq.addEventListener) {
        mobileMq.addEventListener("change", function () { bootMobile(); });
    } else if (mobileMq.addListener) {
        mobileMq.addListener(function () { bootMobile(); });
    }

    // Expose for inline use.
    window.DKMobile = {
        closeAll: closeAll,
    };
})();
