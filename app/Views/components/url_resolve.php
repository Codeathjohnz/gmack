<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Gmack</title>
<style>body{margin:0;background:#f5f6f8;font-family:system-ui,sans-serif;color:#5b6672;display:flex;align-items:center;justify-content:center;height:100vh}</style>
</head>
<body>
<p>Loading…</p>
<script>
(function () {
    var fallback = '<?= base_url('dashboard') ?>';
    var target = null;
    try {
        var code = <?= json_encode($code) ?>;
        var map = JSON.parse(sessionStorage.getItem('gmackUrlMap') || '{}');
        var path = map[code];
        // Only ever go to a path on this same site.
        if (typeof path === 'string' && path.charAt(0) === '/' && path.charAt(1) !== '/') {
            target = window.location.origin + path + window.location.hash;
        }
    } catch (e) {}
    window.location.replace(target || fallback);
})();
</script>
<noscript><meta http-equiv="refresh" content="0;url=<?= base_url('dashboard') ?>"></noscript>
</body>
</html>
