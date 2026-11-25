<?php
/**
 * Address-bar masking. After a page loads, the visible URL is replaced with
 * /v/<random code>. The real address is kept in this tab's sessionStorage,
 * so Refresh and Back still work (see Home::masked). This is cosmetic only:
 * every page still checks the login and role on the server.
 */
?>
<script>
window.addEventListener('load', function () {
    try {
        if (!window.history || !history.replaceState || !window.sessionStorage) return;
        var real = new URL(window.location.href);
        var base = new URL('<?= rtrim(base_url(), '/') ?>/', real);

        // Relative links ("?period=month") and forms with no action would
        // otherwise resolve against the masked address — pin them to the real one.
        document.querySelectorAll('a[href]').forEach(function (a) {
            var h = a.getAttribute('href');
            if (!h || /^(#|[a-z][a-z0-9+.-]*:|\/\/|\/)/i.test(h)) return;
            a.setAttribute('href', new URL(h, real).href);
        });
        document.querySelectorAll('form').forEach(function (f) {
            if (!f.getAttribute('action')) f.setAttribute('action', real.origin + real.pathname);
        });

        var chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        var code = '';
        var rnd = new Uint8Array(10);
        (window.crypto || window.msCrypto).getRandomValues(rnd);
        for (var i = 0; i < rnd.length; i++) code += chars[rnd[i] % chars.length];

        var map = JSON.parse(sessionStorage.getItem('gmackUrlMap') || '{}');
        map[code] = real.pathname + real.search;
        var keys = Object.keys(map);
        if (keys.length > 100) keys.slice(0, keys.length - 100).forEach(function (k) { delete map[k]; });
        sessionStorage.setItem('gmackUrlMap', JSON.stringify(map));

        history.replaceState(history.state, '', base.pathname + 'v/' + code + real.hash);
    } catch (e) { /* if anything fails, just keep the real address */ }
});
</script>
