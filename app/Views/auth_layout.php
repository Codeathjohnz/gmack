<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - Gmack' : 'Gmack — Point of Sale for Construction Supply' ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='32' height='32'%3E%3Crect width='24' height='24' rx='5' fill='%231c2126'/%3E%3Cpath d='M4 15c0-5 3.6-9 8-9s8 4 8 9' fill='none' stroke='%23ff6b00' stroke-width='2' stroke-linecap='round'/%3E%3Cpath d='M12 6v2.6' stroke='%23ff6b00' stroke-width='2' stroke-linecap='round'/%3E%3Crect x='2' y='15' width='20' height='3.2' rx='1.6' fill='%23ffc400'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; width: 100%; }

        :root {
            --steel-900: #14181c;
            --steel-800: #1c2126;
            --steel-700: #262d34;
            --safety-orange: #ff6b00;
            --safety-orange-dark: #d95700;
            --caution-yellow: #ffc400;
            --blueprint-blue: #1d4ed8;
            --concrete-50: #f4f5f7;
            --concrete-100: #eceef1;
            --concrete-300: #cbd2d9;
            --concrete-600: #5b6672;

            --gradient-brand: linear-gradient(135deg, var(--safety-orange) 0%, var(--caution-yellow) 100%);

            /* kept for any legacy references */
            --primary: var(--safety-orange);
            --primary-dark: var(--safety-orange-dark);
            --primary-light: var(--caution-yellow);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--concrete-50);
            color: var(--steel-800);
        }

        h1, h2, h3, .brand-mark-text, .auth-illustration h2 {
            font-family: 'Barlow Condensed', 'Segoe UI', sans-serif;
        }

        /* ================= Shell: split screen ================= */
        .auth-shell { display: flex; min-height: 100vh; }

        /* ---- Left: animated construction/hardware illustration ---- */
        .auth-illustration {
            position: relative;
            flex: 1 1 46%;
            min-height: 100vh;
            background: linear-gradient(165deg, var(--steel-800) 0%, var(--steel-900) 100%);
            color: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 44px 48px;
        }

        /* animated blueprint grid */
        .auth-illustration::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 42px 42px;
            animation: gridDrift 26s linear infinite;
        }
        .auth-illustration::after {
            content: '';
            position: absolute;
            width: 520px; height: 520px;
            right: -180px; bottom: -180px;
            background: radial-gradient(circle, rgba(255,107,0,0.22) 0%, transparent 70%);
            border-radius: 50%;
        }
        @keyframes gridDrift {
            from { background-position: 0 0, 0 0; }
            to { background-position: 42px 42px, 42px 42px; }
        }

        .auth-illustration-top { position: relative; z-index: 2; }
        .auth-illustration-top .brand-mark-primary { color: #fff; }
        .auth-illustration-top .brand-mark-accent { color: var(--caution-yellow); }
        .auth-illustration-top .brand-mark-icon { color: var(--safety-orange); }

        .auth-illustration-body {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 22px;
            max-width: 460px;
        }

        .auth-illustration-body h2 {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0.2px;
        }
        .auth-illustration-body h2 span { color: var(--caution-yellow); }

        .auth-illustration-body p.lede {
            color: rgba(255,255,255,0.78);
            font-size: 1rem;
            line-height: 1.55;
        }

        /* floating construction icon cluster */
        .auth-icon-field {
            position: relative;
            height: 150px;
            margin-top: 6px;
        }
        .auth-icon-field .float-icon {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px; height: 56px;
            border-radius: 14px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            font-size: 1.5rem;
            color: var(--caution-yellow);
            backdrop-filter: blur(2px);
            animation: floatY 5s ease-in-out infinite;
        }
        .float-icon.i1 { left: 0;    top: 10px;  animation-delay: 0s;   color: var(--safety-orange); }
        .float-icon.i2 { left: 90px; top: 60px;  animation-delay: 0.6s; }
        .float-icon.i3 { left: 190px;top: 0;     animation-delay: 1.2s; color: #7fd0ff; }
        .float-icon.i4 { left: 280px;top: 70px;  animation-delay: 1.8s; }
        .float-icon.i5 { left: 370px;top: 20px;  animation-delay: 2.4s; color: var(--safety-orange); }
        @keyframes floatY {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        .auth-feature-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .auth-feature-list li {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.92rem; font-weight: 500;
            color: rgba(255,255,255,0.9);
            opacity: 0;
            animation: featureIn 0.5s ease forwards;
        }
        .auth-feature-list li:nth-child(1) { animation-delay: 0.15s; }
        .auth-feature-list li:nth-child(2) { animation-delay: 0.35s; }
        .auth-feature-list li:nth-child(3) { animation-delay: 0.55s; }
        @keyframes featureIn {
            from { opacity: 0; transform: translateX(-8px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .auth-feature-list i {
            display: inline-flex; align-items: center; justify-content: center;
            width: 26px; height: 26px; border-radius: 50%;
            background: var(--gradient-brand); color: var(--steel-900); font-size: 0.8rem;
        }

        .auth-illustration-foot {
            position: relative; z-index: 2;
            font-size: 0.78rem;
            color: rgba(255,255,255,0.55);
            letter-spacing: 0.3px;
        }

        /* ---- Right: form panel ---- */
        .auth-panel {
            flex: 1 1 54%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            min-height: 100vh;
        }

        .auth-container { width: 100%; max-width: 440px; }

        .auth-mobile-brand { display: none; }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(20, 24, 28, 0.12);
            overflow: hidden;
            width: 100%;
            animation: cardIn 0.5s ease both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header { padding: 8px 4px 20px; text-align: left; }
        .auth-header h1 { font-size: 1.7rem; font-weight: 800; margin-bottom: 6px; color: var(--steel-800); }
        .auth-header p { font-size: 0.92rem; color: var(--concrete-600); font-weight: 500; }

        .auth-body { padding: 4px 4px 8px; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 0.9rem; }

        .form-control {
            width: 100%; padding: 13px 15px; border: 2px solid var(--concrete-300);
            border-radius: 8px; font-size: 1rem; font-family: inherit; transition: all 0.25s ease;
        }
        .form-control:focus { outline: none; border-color: var(--safety-orange); box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.14); }
        .form-control::placeholder { color: #aaa; }

        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #666; font-size: 1.1rem;
            padding: 5px; display: flex; align-items: center; justify-content: center;
            width: 35px; height: 35px; margin-top: 2px;
        }
        .password-toggle:hover { color: var(--safety-orange); }

        .checkbox-group { display: flex; align-items: center; margin-bottom: 22px; }
        .checkbox-group input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; accent-color: var(--safety-orange); margin-right: 10px; }
        .checkbox-group label { cursor: pointer; margin-bottom: 0; color: #666; font-weight: 500; user-select: none; }

        .btn { width: 100%; padding: 14px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.25s ease; text-transform: uppercase; letter-spacing: 0.8px; }
        .btn-primary { background: var(--gradient-brand); color: var(--steel-900); margin-bottom: 14px; }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255, 107, 0, 0.3); }
        .btn-primary:active { transform: translateY(-1px); }
        .btn-secondary { background: var(--concrete-100); color: var(--steel-800); border: 2px solid var(--concrete-300); }
        .btn-secondary:hover { background: #e4e7eb; border-color: var(--concrete-300); }

        .auth-footer { padding: 20px 4px 4px; text-align: left; font-size: 0.85rem; color: #666; border-top: 1px solid var(--concrete-100); margin-top: 6px; }
        .auth-footer a { color: var(--safety-orange); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { color: var(--safety-orange-dark); text-decoration: underline; }

        .alert { padding: 14px 16px; border-radius: 8px; margin-bottom: 22px; font-size: 0.88rem; display: flex; align-items: flex-start; gap: 12px; border: 1px solid; }
        .alert-danger { background: #fdf1ee; color: #9a2f0a; border-color: #f6cdbb; }
        .alert-success { background: #eef8f0; color: #17663a; border-color: #c9e9d0; }
        .alert-icon { font-size: 1.05rem; flex-shrink: 0; margin-top: 1px; }

        .form-error { color: #c33; font-size: 0.8rem; margin-top: 5px; display: none; }
        .form-control.error { border-color: #c33; }
        .form-control.error:focus { box-shadow: 0 0 0 3px rgba(204, 51, 51, 0.1); }

        .divider { text-align: center; margin: 22px 0; position: relative; color: #999; font-size: 0.82rem; }
        .divider::before { content: ''; position: absolute; left: 0; top: 50%; width: 100%; height: 1.5px; background: linear-gradient(90deg, transparent, var(--concrete-300), transparent); }
        .divider span { background: white; padding: 0 12px; position: relative; }

        .loading { opacity: 0.6; pointer-events: none; }
        .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(20,24,28,0.25); border-radius: 50%; border-top-color: var(--steel-900); animation: spin 0.8s linear infinite; margin-right: 8px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 991px) {
            .auth-illustration { display: none; }
            .auth-mobile-brand { display: block; text-align: center; margin-bottom: 22px; }
            .auth-mobile-brand .brand-mark { justify-content: center; }
            .auth-mobile-brand p { font-size: 0.8rem; color: var(--concrete-600); font-weight: 600; margin-top: 4px; }
        }

        @media (max-width: 480px) {
            .auth-panel { padding: 24px 16px; }
            .auth-header h1 { font-size: 1.45rem; }
            .btn { padding: 12px 18px; font-size: 0.85rem; }
            .form-control { padding: 12px 14px; font-size: 16px; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <!-- Animated Hardware/Construction illustration -->
        <aside class="auth-illustration">
            <div class="auth-illustration-top">
                <?= view('components/logo', ['size' => 'md', 'link' => false]) ?>
            </div>

            <div class="auth-illustration-body">
                <h2>One Smart POS.<br>Any <span>Construction Supply Business.</span></h2>
                <p class="lede">Gmack gives any hardware &amp; construction supply business its own point of sale — inventory, cashiering, and sales analytics, fully isolated from every other business on the platform.</p>

                <div class="auth-icon-field" aria-hidden="true">
                    <span class="float-icon i1"><i class="bi bi-cone-striped"></i></span>
                    <span class="float-icon i2"><i class="bi bi-tools"></i></span>
                    <span class="float-icon i3"><i class="bi bi-rulers"></i></span>
                    <span class="float-icon i4"><i class="bi bi-bricks"></i></span>
                    <span class="float-icon i5"><i class="bi bi-graph-up-arrow"></i></span>
                </div>

                <ul class="auth-feature-list">
                    <li><i class="bi bi-buildings"></i> Multi-Business Ready</li>
                    <li><i class="bi bi-bar-chart-line"></i> Real-Time Sales Analytics</li>
                    <li><i class="bi bi-box-seam"></i> Smart Inventory Alerts</li>
                </ul>
            </div>

            <div class="auth-illustration-foot">
                &copy; <?= date('Y') ?> Gmack — Point of Sale for Construction Supply.
            </div>
        </aside>

        <!-- Form panel -->
        <div class="auth-panel">
            <div class="auth-container">
                <div class="auth-mobile-brand">
                    <?= view('components/logo', ['size' => 'md', 'link' => false]) ?>
                    <p>Point of Sale for Construction Supply</p>
                </div>
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= view('components/url_mask') ?>
</body>
</html>
