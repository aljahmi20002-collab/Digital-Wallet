"use strict";

/**
 * Payment Links — frontend interactions.
 *
 * Owns three behaviours, all wired declaratively via data-* attributes:
 *
 *   1. Index page: copy-to-clipboard for the public payment URL and
 *      delete-confirmation prompt.
 *   2. Create / edit form: when a merchant shop is selected, limit the
 *      currency selector to that shop's supported currencies, render a
 *      small shop preview card, and clear it again when unselected.
 *   3. Public checkout: disable the submit button + show a spinner once
 *      the wallet payment form is submitted, so the payer can't double
 *      submit.
 *
 * No visual styling is set from JS; state is communicated via class
 * toggles only.
 */
(function () {
    var COPY_RESET_MS = 1800;

    var notify = function (type, message) {
        if (typeof window.notifyEvs === 'function') {
            window.notifyEvs(type, message);
        }
    };

    var swapCopyVisuals = function (copyBtn) {
        var icon  = copyBtn.querySelector('[data-copy-icon]');
        var label = copyBtn.querySelector('[data-copy-label]');

        copyBtn.classList.remove('btn-outline-primary', 'btn-outline-secondary');
        copyBtn.classList.add('btn-success', 'is-copied');

        if (icon) {
            icon.dataset.originalClass = icon.dataset.originalClass || icon.className;
            icon.className = 'fas fa-check';
        }
        if (label) {
            label.dataset.originalText = label.dataset.originalText || label.textContent;
            label.textContent = label.getAttribute('data-copied-label') || 'Copied';
        }

        window.setTimeout(function () {
            copyBtn.classList.remove('btn-success', 'is-copied');
            copyBtn.classList.add('btn-outline-primary');
            if (icon && icon.dataset.originalClass) {
                icon.className = icon.dataset.originalClass;
            }
            if (label && label.dataset.originalText) {
                label.textContent = label.dataset.originalText;
            }
        }, COPY_RESET_MS);
    };

    var copyText = function (text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            try {
                var temp = document.createElement('textarea');
                temp.value = text;
                temp.setAttribute('readonly', 'readonly');
                temp.style.position = 'fixed';
                temp.style.opacity  = '0';
                document.body.appendChild(temp);
                temp.select();
                var ok = document.execCommand('copy');
                document.body.removeChild(temp);
                ok ? resolve() : reject(new Error('execCommand failed'));
            } catch (err) {
                reject(err);
            }
        });
    };

    var escapeHtml = function (value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char];
        });
    };

    var printPaymentQr = function (button) {
        var targetId = button.getAttribute('data-print-target');
        var target   = targetId ? document.getElementById(targetId) : null;
        if (!target) {
            notify('error', 'QR code is not ready for printing.');
            return;
        }

        var title  = button.getAttribute('data-print-title') || 'Payment QR Code';
        var amount = button.getAttribute('data-print-amount') || '';
        var url    = button.getAttribute('data-print-url') || '';
        var popup  = window.open('', 'payment_link_qr_print', 'width=520,height=680');

        if (!popup) {
            notify('error', 'Please allow popups to print the QR code.');
            return;
        }

        popup.document.open();
        popup.document.write([
            '<!doctype html>',
            '<html>',
            '<head>',
            '<meta charset="utf-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<title>Print QR Code</title>',
            '<style>',
            'body{margin:0;padding:28px;font-family:Arial,sans-serif;background:#f5f7fb;color:#111827;text-align:center;}',
            '.sheet{max-width:420px;margin:0 auto;padding:28px;border-radius:22px;background:#fff;box-shadow:0 18px 45px rgba(15,23,42,.12);}',
            '.eyebrow{margin:0 0 6px;color:#2563eb;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;}',
            'h1{margin:0;font-size:21px;line-height:1.2;}',
            '.amount{margin:10px 0 18px;color:#475467;font-size:14px;font-weight:700;}',
            '.qr{display:inline-block;padding:14px;border:1px solid #e4e7ec;border-radius:18px;background:#fff;}',
            '.qr svg{display:block;width:260px;max-width:100%;height:auto;}',
            '.url{margin:18px 0 0;word-break:break-all;color:#475467;font-size:12px;line-height:1.45;}',
            '.hint{margin:14px 0 0;color:#667085;font-size:12px;}',
            '@media print{body{background:#fff;padding:0}.sheet{box-shadow:none;border:0}.no-print{display:none}}',
            '</style>',
            '</head>',
            '<body>',
            '<main class="sheet">',
            '<p class="eyebrow">Scan to Pay</p>',
            '<h1>' + escapeHtml(title) + '</h1>',
            amount ? '<p class="amount">' + escapeHtml(amount) + '</p>' : '',
            '<div class="qr">' + target.innerHTML + '</div>',
            url ? '<p class="url">' + escapeHtml(url) + '</p>' : '',
            '<p class="hint">Scan this QR code to open the secure checkout.</p>',
            '</main>',
            '<script>window.addEventListener("load",function(){setTimeout(function(){window.focus();window.print();},120);});<\/script>',
            '</body>',
            '</html>',
        ].join(''));
        popup.document.close();
    };

    document.addEventListener('click', function (event) {
        var copyBtn = event.target.closest('[data-payment-link-copy]');
        if (copyBtn) {
            event.preventDefault();
            var wrapper = copyBtn.closest('[data-payment-link-group]') || copyBtn.closest('.input-group');
            var input   = wrapper ? wrapper.querySelector('[data-payment-link-url]') : null;
            if (!input) {
                return;
            }

            copyText(input.value).then(function () {
                swapCopyVisuals(copyBtn);
                notify('success', copyBtn.getAttribute('data-toast-message') || 'Payment link copied to clipboard');
            }).catch(function () {
                notify('error', 'Could not copy. Please copy the link manually.');
            });

            return;
        }

        var printBtn = event.target.closest('[data-payment-link-print]');
        if (printBtn) {
            event.preventDefault();
            printPaymentQr(printBtn);
            return;
        }

        var deleteBtn = event.target.closest('[data-payment-link-delete]');
        if (deleteBtn) {
            if (!window.confirm('Delete this payment link? This cannot be undone.')) {
                event.preventDefault();
            }
        }
    });

    /* ---------- Form: merchant selector ---------- */

    var merchantSelect = document.querySelector('[data-payment-link-merchant]');
    if (merchantSelect) {
        var merchants = {};
        try {
            merchants = JSON.parse(merchantSelect.getAttribute('data-merchants') || '{}');
        } catch (e) {
            merchants = {};
        }

        var currencySelect = document.querySelector('[data-payment-link-currency]');
        var preview        = document.querySelector('[data-payment-link-merchant-preview]');
        var previewLogo    = preview ? preview.querySelector('[data-merchant-preview-logo]') : null;
        var previewName    = preview ? preview.querySelector('[data-merchant-preview-name]') : null;
        var previewCcy     = preview ? preview.querySelector('[data-merchant-preview-currency]') : null;
        var previewFee     = preview ? preview.querySelector('[data-merchant-preview-fee]') : null;

        var currenciesFor = function (meta) {
            if (!meta) {
                return [];
            }

            if (Array.isArray(meta.currencies) && meta.currencies.length > 0) {
                return meta.currencies;
            }

            if (meta.currency_code) {
                return [{
                    id: meta.currency_id,
                    code: meta.currency_code,
                    name: meta.currency_name,
                }];
            }

            return [];
        };

        var currencyCodesFor = function (meta) {
            return currenciesFor(meta).map(function (currency) {
                return currency && currency.code ? currency.code : '';
            }).filter(Boolean).join(', ');
        };

        var applyCurrencyFilter = function (meta) {
            if (!currencySelect) {
                return;
            }

            var currencies = currenciesFor(meta);
            var supported  = {};
            var preferred  = meta && meta.currency_id ? String(meta.currency_id) : '';
            var first      = '';

            if (!meta || currencies.length === 0) {
                for (var clearIndex = 0; clearIndex < currencySelect.options.length; clearIndex += 1) {
                    if (currencySelect.options[clearIndex].value !== '') {
                        currencySelect.options[clearIndex].disabled = false;
                    }
                }
                currencySelect.removeAttribute('disabled');
                currencySelect.classList.remove('payment-link-locked-control');
                currencySelect.removeAttribute('data-payment-link-no-match');
                return;
            }

            currencies.forEach(function (currency) {
                if (currency && currency.id != null) {
                    supported[String(currency.id)] = true;
                }
            });

            for (var i = 0; i < currencySelect.options.length; i += 1) {
                var opt = currencySelect.options[i];

                if (opt.value === '') {
                    continue;
                }

                var isSupported = Object.prototype.hasOwnProperty.call(supported, opt.value);
                opt.disabled = !isSupported;

                if (isSupported && first === '') {
                    first = opt.value;
                }
            }

            if (!Object.prototype.hasOwnProperty.call(supported, currencySelect.value)) {
                currencySelect.value = preferred && Object.prototype.hasOwnProperty.call(supported, preferred)
                    ? preferred
                    : first;
            }

            currencySelect.removeAttribute('disabled');
            currencySelect.classList.add('payment-link-locked-control');

            if (!first) {
                currencySelect.setAttribute('data-payment-link-no-match', 'true');
            } else {
                currencySelect.removeAttribute('data-payment-link-no-match');
            }
        };

        var applyMerchant = function () {
            var id   = merchantSelect.value;
            var meta = id && Object.prototype.hasOwnProperty.call(merchants, id) ? merchants[id] : null;

            if (meta) {
                applyCurrencyFilter(meta);

                if (preview) {
                    if (previewLogo)    { previewLogo.setAttribute('src', meta.business_logo || ''); previewLogo.setAttribute('alt', meta.business_name || ''); }
                    if (previewName)    { previewName.textContent = meta.business_name || ''; }
                    if (previewCcy)     { previewCcy.textContent = currencyCodesFor(meta) || meta.currency_code || ''; }
                    if (previewFee)     { previewFee.textContent = (meta.fee != null ? meta.fee : 0).toString(); }
                    preview.classList.remove('d-none');
                    preview.classList.add('d-flex');
                }
            } else {
                applyCurrencyFilter(null);
                if (preview) {
                    preview.classList.add('d-none');
                    preview.classList.remove('d-flex');
                }
            }
        };

        merchantSelect.addEventListener('change', applyMerchant);
        // Apply once on load so reselected merchant + locked currency are
        // restored after a validation error redirect.
        applyMerchant();
    }

    /* ---------- Public checkout: submit-once + spinner ---------- */

    var checkoutForm = document.getElementById('paymentLinkForm');
    if (checkoutForm) {
        var processingLabel = checkoutForm.getAttribute('data-processing-label') || 'Processing...';
        var payButton       = document.getElementById('payNowBtn');
        var selectedMethod  = document.getElementById('paymentLinkSelectedMethod');
        var methodCards     = checkoutForm.querySelectorAll('[data-payment-link-method]');
        var walletFields    = checkoutForm.querySelector('[data-wallet-payment-fields]');
        var walletInputs    = checkoutForm.querySelectorAll('[data-wallet-payment-input]');
        var securityFooter  = document.getElementById('paymentSecurityFooter');
        var securityText    = securityFooter ? securityFooter.querySelector('span') : null;
        var walletMethod    = 'system';

        var applyPaymentMethod = function (method) {
            if (selectedMethod) {
                selectedMethod.value = method;
            }

            methodCards.forEach(function (card) {
                var isActive = card.getAttribute('data-payment-link-method') === method;
                card.classList.toggle('active', isActive);
                card.classList.toggle('selected', isActive);
                card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            var isWallet = method === walletMethod;
            if (walletFields) {
                walletFields.classList.toggle('d-none', !isWallet);
            }

            walletInputs.forEach(function (input) {
                if (isWallet) {
                    input.removeAttribute('disabled');
                } else {
                    input.setAttribute('disabled', 'disabled');
                }
            });

            if (securityText && securityFooter) {
                securityText.textContent = isWallet
                    ? securityFooter.getAttribute('data-wallet-label')
                    : securityFooter.getAttribute('data-gateway-label');
            }
        };

        methodCards.forEach(function (card) {
            card.addEventListener('click', function () {
                applyPaymentMethod(this.getAttribute('data-payment-link-method') || walletMethod);
            });

            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.click();
                }
            });
        });

        applyPaymentMethod(selectedMethod ? selectedMethod.value : walletMethod);

        checkoutForm.querySelectorAll('.wallet-pin-input').forEach(function (input) {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D+/g, '').slice(0, 6);
            });
        });

        checkoutForm.addEventListener('submit', function () {
            if (payButton) {
                payButton.setAttribute('disabled', 'disabled');
                payButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
                    + processingLabel;
            }
        });
    }
})();
