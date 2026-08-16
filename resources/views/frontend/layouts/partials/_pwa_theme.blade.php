<script>
    'use strict';
    (function () {
        try {
            var stored = localStorage.getItem("digikash-theme");
            var prefersLight = window.matchMedia && window.matchMedia("(prefers-color-scheme: light)").matches;
            document.documentElement.setAttribute("data-theme", stored || (prefersLight ? "light" : "dark"));
        } catch (e) {
            document.documentElement.setAttribute("data-theme", "dark");
        }
    })();
</script>