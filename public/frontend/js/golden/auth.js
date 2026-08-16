/* =====================================================
   Golden Theme — Auth Page Interactions
   Loaded by frontend.layouts.golden.auth.

   Each module below scopes itself defensively so the bundle
   can be included on every auth screen without conditionals.
   ===================================================== */

(function () {
    'use strict';

    /* ----- 1. Particle drift on brand panel ----- */
    function initParticles() {
        document.querySelectorAll('[data-particles]').forEach(function (layer) {
            if (layer.dataset.bound === '1') { return; }
            layer.dataset.bound = '1';
            var count = 22;
            for (var i = 0; i < count; i++) {
                var p = document.createElement('span');
                p.className = 'particle';
                var size = 1.5 + Math.random() * 2.5;
                p.style.width = p.style.height = size + 'px';
                p.style.left = (Math.random() * 100) + '%';
                p.style.bottom = (-10 - Math.random() * 30) + '%';
                var dur = 16 + Math.random() * 22;
                p.style.animationDuration = dur + 's';
                p.style.animationDelay = (-Math.random() * dur) + 's';
                p.style.opacity = (0.35 + Math.random() * 0.5);
                layer.appendChild(p);
            }
        });
    }

    /* ----- 2. Password eye toggle ----- */
    function initEyes() {
        document.querySelectorAll('[data-eye]').forEach(function (btn) {
            if (btn.dataset.bound === '1') { return; }
            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                var id = btn.dataset.eye;
                var input = document.getElementById(id);
                var icon = btn.querySelector('i');
                if (!input) { return; }
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) { icon.className = 'fa-regular fa-eye-slash'; }
                } else {
                    input.type = 'password';
                    if (icon) { icon.className = 'fa-regular fa-eye'; }
                }
            });
        });
    }

    /* ----- 3. Password strength meter ----- */
    function scorePassword(v) {
        var s = 0;
        if (v.length >= 8) { s++; }
        if (v.length >= 12) { s++; }
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) { s++; }
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) { s++; }
        return Math.min(4, s);
    }

    function initStrength() {
        var labels = ['Awaiting input', 'Weak', 'Fair', 'Strong', 'Sovereign'];
        document.querySelectorAll('input[data-strength]').forEach(function (input) {
            if (input.dataset.bound === '1') { return; }
            input.dataset.bound = '1';
            input.addEventListener('input', function () {
                var s = input.value ? scorePassword(input.value) : 0;
                var pips = document.querySelectorAll('[data-meter-for="' + input.id + '"] .meter__pip');
                pips.forEach(function (p, i) { p.classList.toggle('is-on', i < s); });
                var lbl = document.querySelector('[data-meter-lbl="' + input.id + '"]');
                if (lbl) {
                    lbl.textContent = 'Strength · ' + labels[s];
                    lbl.classList.toggle('is-strong', s >= 3);
                }
            });
        });
    }

    /* ----- 4. Confirm-password match indicator ----- */
    function initMatch() {
        document.querySelectorAll('input[data-confirm]').forEach(function (input) {
            if (input.dataset.bound === '1') { return; }
            input.dataset.bound = '1';
            var sourceId = input.dataset.confirm;
            var source = document.getElementById(sourceId);
            var icon = input.parentElement.querySelector('[data-match]');
            var field = input.closest('.field');

            function update() {
                var a = source && source.value;
                var b = input.value;
                var pips = document.querySelectorAll('[data-meter-for="' + input.id + '"] .meter__pip');
                var lbl = document.querySelector('[data-meter-lbl="' + input.id + '"]');
                if (!b) {
                    if (icon) { icon.className = 'fa-solid fa-check'; }
                    if (field) { field.classList.remove('has-error'); }
                    if (lbl) { lbl.textContent = 'Match · Awaiting input'; lbl.classList.remove('is-strong'); }
                    pips.forEach(function (p) { p.classList.remove('is-on'); });
                    return;
                }
                if (a && b && a === b) {
                    if (icon) { icon.className = 'fa-solid fa-check is-success'; }
                    if (field) { field.classList.remove('has-error'); }
                    if (lbl) { lbl.textContent = 'Match · Confirmed'; lbl.classList.add('is-strong'); }
                    pips.forEach(function (p) { p.classList.add('is-on'); });
                } else {
                    if (icon) { icon.className = 'fa-solid fa-xmark is-error'; }
                    if (field) { field.classList.add('has-error'); }
                    if (lbl) { lbl.textContent = 'Match · Mismatch'; lbl.classList.remove('is-strong'); }
                    pips.forEach(function (p) { p.classList.remove('is-on'); });
                }
            }

            input.addEventListener('input', update);
            if (source) { source.addEventListener('input', update); }
        });
    }

    /* ----- 5. OTP — auto-advance, paste, ring countdown ----- */
    function initOtp() {
        var group = document.querySelector('[data-otp-group]');
        if (!group) { return; }
        if (group.dataset.bound === '1') { return; }
        group.dataset.bound = '1';

        var cells = Array.prototype.slice.call(group.querySelectorAll('.otp__cell'));
        var hidden = document.querySelector('[data-otp-hidden]');
        var submit = document.querySelector('[data-otp-submit]');

        function syncHidden() {
            cells.forEach(function (c) { c.classList.toggle('is-filled', !!c.value); });
            var joined = cells.map(function (c) { return c.value; }).join('');
            if (hidden) { hidden.value = joined; }
            if (submit) { submit.disabled = cells.some(function (c) { return !c.value; }); }
        }

        cells.forEach(function (cell, idx) {
            cell.addEventListener('input', function () {
                cell.value = cell.value.replace(/\D/g, '').slice(-1);
                if (cell.value && idx < cells.length - 1) { cells[idx + 1].focus(); }
                syncHidden();
            });
            cell.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !cell.value && idx > 0) {
                    cells[idx - 1].focus();
                } else if (e.key === 'ArrowLeft' && idx > 0) {
                    cells[idx - 1].focus();
                } else if (e.key === 'ArrowRight' && idx < cells.length - 1) {
                    cells[idx + 1].focus();
                }
            });
            cell.addEventListener('paste', function (e) {
                e.preventDefault();
                var text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, cells.length);
                Array.prototype.forEach.call(text, function (ch, i) { if (cells[i]) { cells[i].value = ch; } });
                var lastIdx = Math.min(text.length, cells.length) - 1;
                if (lastIdx >= 0) { cells[Math.min(lastIdx + 1, cells.length - 1)].focus(); }
                syncHidden();
            });
        });

        var form = group.closest('form');
        if (form) {
            form.addEventListener('submit', syncHidden);
        }

        // Countdown ring — 30s loop, anchored to wall-clock for TOTP accuracy.
        var ring = document.querySelector('[data-otp-ring]');
        var lbl = document.querySelector('[data-otp-ring-label]');
        if (ring && lbl) {
            var circumference = 125.66; // 2π × 20
            function tick() {
                var seconds = 30 - (Math.floor(Date.now() / 1000) % 30);
                lbl.textContent = seconds;
                ring.style.strokeDashoffset = ((30 - seconds) / 30) * circumference;
                setTimeout(tick, 1000);
            }
            tick();
        }
    }

    /* ----- 6. Resend link countdown ----- */
    function initResend() {
        var btn = document.querySelector('[data-resend]');
        if (!btn) { return; }
        if (btn.dataset.bound === '1') { return; }
        btn.dataset.bound = '1';
        var originalHtml = btn.innerHTML;
        var timer = null;

        function start() {
            var t = parseInt(btn.dataset.resendSeconds || '60', 10);
            btn.disabled = true;
            btn.classList.add('is-counting');
            function update() {
                var m = Math.floor(t / 60);
                var s = String(t % 60).padStart(2, '0');
                btn.textContent = 'Resend in ' + m + ':' + s;
                if (t <= 0) {
                    clearInterval(timer);
                    timer = null;
                    btn.disabled = false;
                    btn.classList.remove('is-counting');
                    btn.innerHTML = originalHtml;
                    return;
                }
                t--;
            }
            update();
            timer = setInterval(update, 1000);
        }

        btn.addEventListener('click', function (e) {
            if (btn.disabled) { e.preventDefault(); return; }
            if (btn.type !== 'submit' && !btn.closest('form')) { e.preventDefault(); }
            start();
        });

        if (btn.dataset.resendAutostart === '1') {
            start();
        }
    }

    /* ----- 7. Alert dismiss ----- */
    function initAlerts() {
        document.querySelectorAll('[data-alert-close]').forEach(function (btn) {
            if (btn.dataset.bound === '1') { return; }
            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                var alert = btn.closest('.alert');
                if (alert) { alert.classList.remove('is-visible'); }
            });
        });
    }

    function boot() {
        initParticles();
        initEyes();
        initStrength();
        initMatch();
        initOtp();
        initResend();
        initAlerts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
