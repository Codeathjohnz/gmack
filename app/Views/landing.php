<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmack — Point of Sale for Construction Supply</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='32' height='32'%3E%3Crect width='24' height='24' rx='5' fill='%231c2126'/%3E%3Cpath d='M4 15c0-5 3.6-9 8-9s8 4 8 9' fill='none' stroke='%23ff6b00' stroke-width='2' stroke-linecap='round'/%3E%3Cpath d='M12 6v2.6' stroke='%23ff6b00' stroke-width='2' stroke-linecap='round'/%3E%3Crect x='2' y='15' width='20' height='3.2' rx='1.6' fill='%23ffc400'/%3E%3C/svg%3E">
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/icon-192.png') ?>">
    <meta name="theme-color" content="#d95700">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Gmack">
    <style>
        :root {
            /* Hardware & Construction industrial palette */
            --clr-yellow: #ffc400;
            --clr-amber: #ffa000;
            --clr-orange: #ff6b00;
            --clr-pink: #d95700;
            --clr-magenta: #262d34;
            --clr-deep: #14181c;

            --gradient-brand: linear-gradient(135deg, var(--clr-yellow) 0%, var(--clr-orange) 32%, var(--clr-pink) 68%, var(--clr-deep) 100%);
            --gradient-brand-reverse: linear-gradient(135deg, var(--clr-deep) 0%, var(--clr-pink) 40%, var(--clr-orange) 78%, var(--clr-yellow) 100%);
            --gradient-brand-soft: linear-gradient(135deg, rgba(255, 196, 0, 0.14) 0%, rgba(255, 107, 0, 0.14) 32%, rgba(217, 87, 0, 0.14) 68%, rgba(20, 24, 28, 0.14) 100%);

            --ink: #20262c;
            --ink-soft: #4a545c;
            --muted: #6b7680;
            --surface: #ffffff;
            --surface-alt: #f6f4f1;
            --border: rgba(20, 24, 28, 0.12);
            --shadow-brand: rgba(38, 45, 52, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--ink);
            background: var(--surface);
        }

        a {
            font-family: inherit;
        }

        ::selection {
            background: var(--clr-pink);
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .gradient-text {
            background: var(--gradient-brand);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Navigation */
        nav {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            padding: 0.85rem 2rem;
            box-shadow: 0 2px 16px rgba(20, 24, 28, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 3px solid transparent;
            border-image: var(--gradient-brand) 1;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .gmack-logo-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .gmack-logo {
            filter: drop-shadow(0 2px 6px rgba(20, 24, 28, 0.15));
        }

        nav .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        /* ---- Mobile menu toggle (hidden on desktop; shown below 640px) ---- */
        .nav-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 38px;
            height: 38px;
            padding: 0;
            border: none;
            border-radius: 8px;
            background: transparent;
            cursor: pointer;
            flex-shrink: 0;
        }

        .nav-toggle:hover { background: rgba(20, 24, 28, 0.06); }

        .nav-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            border-radius: 2px;
            background: var(--clr-magenta);
            transition: transform 0.25s ease, opacity 0.2s ease;
        }

        .nav-toggle[aria-expanded="true"] span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .nav-toggle[aria-expanded="true"] span:nth-child(2) { opacity: 0; }
        .nav-toggle[aria-expanded="true"] span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        nav a.nav-link {
            color: var(--ink-soft);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.25s;
            position: relative;
        }

        nav a.nav-link:hover {
            color: var(--clr-pink);
        }

        nav a.nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 0;
            height: 2px;
            background: var(--gradient-brand);
            transition: width 0.25s;
        }

        nav a.nav-link:hover::after {
            width: 100%;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: var(--gradient-brand);
            color: white;
            box-shadow: 0 8px 20px var(--shadow-brand);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px var(--shadow-brand);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .btn-secondary:hover {
            background: white;
            color: var(--clr-magenta);
        }

        .btn-nav {
            padding: 0.55rem 1.4rem;
            font-size: 0.9rem;
        }

        .btn-nav-outline {
            background: transparent;
            color: var(--clr-magenta);
            border: 2px solid var(--clr-magenta);
            box-shadow: none;
        }

        .btn-nav-outline:hover {
            background: var(--clr-magenta);
            color: white;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            position: relative;
            background: var(--gradient-brand);
            color: white;
            padding: 5rem 2rem 6rem;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(255, 255, 255, 0.16) 0, transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(255, 255, 255, 0.14) 0, transparent 45%);
            pointer-events: none;
        }

        .hero .container {
            position: relative;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .hero .container { grid-template-columns: 1fr; text-align: center; }
            .hero-copy { max-width: 100%; margin: 0 auto; }
            .hero-art { order: -1; }
            .hero-art img { width: 220px; height: 300px; }
            .hero-art-badge { left: 50%; transform: translateX(-50%); bottom: -0.75rem; }
        }

        .hero .eyebrow {
            background: rgba(255, 255, 255, 0.18);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
        }

        .hero p {
            font-size: 1.2rem;
            margin: 0 auto 2rem;
            max-width: 640px;
            opacity: 0.95;
        }

        .hero .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        @media (max-width: 900px) {
            .hero .cta-buttons { justify-content: center; }
        }

        .hero .btn-primary {
            background: white;
            color: var(--clr-magenta);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .hero .btn-primary:hover {
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
        }

        /* Promo Section */
        .promo {
            padding: 4.5rem 2rem;
            background: var(--surface);
        }

        .promo .container {
            max-width: 1200px;
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.75rem;
        }

        .promo-card {
            background: var(--surface);
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(20, 24, 28, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .promo-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px var(--shadow-brand);
        }

        .promo-card .promo-img-wrap {
            aspect-ratio: 1 / 1;
            background: var(--surface-alt);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .promo-card img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.75rem;
        }

        .promo-badge {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            background: var(--gradient-brand);
            color: white;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            box-shadow: 0 4px 10px var(--shadow-brand);
        }

        .promo-card .promo-body {
            padding: 1rem 1.1rem 1.25rem;
        }

        .promo-card h3 {
            font-size: 1rem;
            color: var(--ink);
            margin-bottom: 0.4rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .promo-price-original {
            color: var(--muted);
            text-decoration: line-through;
            font-size: 0.85rem;
            margin-right: 0.4rem;
        }

        .promo-price {
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--clr-magenta);
        }

        /* Category Showcase Section */
        .categories {
            padding: 4.5rem 2rem;
            background: var(--surface-alt);
        }

        .categories .container {
            max-width: 1200px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
        }

        .category-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            box-shadow: 0 2px 8px rgba(20, 24, 28, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .category-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px var(--shadow-brand);
        }

        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .category-card .category-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(0deg, rgba(0, 0, 0, 0.72) 0%, rgba(0, 0, 0, 0) 55%);
            display: flex;
            align-items: flex-end;
            padding: 0.9rem;
        }

        .category-card .category-label {
            color: white;
            font-weight: 800;
            font-size: 1rem;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }

        .category-credits {
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 1.5rem;
        }

        .category-credits a {
            color: var(--muted);
            text-decoration: underline;
        }

        /* Features Section */
        .features {
            padding: 4.5rem 2rem;
            background: var(--surface-alt);
        }

        .features .container {
            max-width: 1200px;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-heading h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }

        .section-heading p {
            color: var(--muted);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--surface);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(20, 24, 28, 0.08);
            border: 1px solid var(--border);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px var(--shadow-brand);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1.25rem;
            border-radius: 16px;
            background: var(--gradient-brand);
            box-shadow: 0 8px 18px var(--shadow-brand);
        }

        .feature-card h3 {
            margin-bottom: 0.75rem;
            color: var(--ink);
        }

        .feature-card p {
            color: var(--muted);
            font-size: 0.95rem;
        }

        /* Stats Section */
        .stats {
            background: var(--gradient-brand-reverse);
            color: white;
            padding: 3.5rem 2rem;
        }

        .stats .container {
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            opacity: 0.9;
        }

        /* Quick Start Section */
        .quick-start {
            padding: 4.5rem 2rem;
        }

        .quick-start .container {
            max-width: 1200px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .step {
            position: relative;
            padding: 2rem;
            background: var(--surface-alt);
            border-radius: 12px;
            border-left: 4px solid transparent;
            border-image: var(--gradient-brand) 1;
        }

        .step-number {
            position: absolute;
            top: -18px;
            left: 20px;
            width: 42px;
            height: 42px;
            background: var(--gradient-brand);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            box-shadow: 0 6px 14px var(--shadow-brand);
        }

        .step h3 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            color: var(--ink);
        }

        .step p {
            color: var(--muted);
            font-size: 0.95rem;
        }

        /* Install App */
        .install-app {
            padding: 4.5rem 2rem;
            background: var(--surface-alt);
        }

        .install-card {
            max-width: 640px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: 0 12px 32px rgba(20, 24, 28, 0.1);
        }

        .install-icon {
            width: 84px;
            height: 84px;
            border-radius: 20px;
            box-shadow: 0 10px 24px var(--shadow-brand);
            margin-bottom: 1.5rem;
        }

        .install-card h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 0.75rem;
        }

        .install-card p {
            color: var(--muted);
            margin-bottom: 1.75rem;
        }

        #installFallback {
            display: none;
            text-align: left;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            color: var(--ink-soft);
        }

        #installFallback strong {
            color: var(--clr-magenta);
        }

        #installedMsg {
            display: none;
            font-weight: 700;
            color: var(--clr-magenta);
        }

        /* Final CTA */
        .final-cta {
            margin: 0 2rem 4.5rem;
        }

        .final-cta .inner {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--gradient-brand);
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            color: white;
            box-shadow: 0 20px 40px var(--shadow-brand);
        }

        .final-cta h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
        }

        .final-cta p {
            opacity: 0.95;
            margin-bottom: 1.75rem;
        }

        /* Footer */
        footer {
            background: var(--ink);
            color: rgba(255, 255, 255, 0.85);
            padding: 2.5rem 2rem;
        }

        footer .container {
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            text-align: center;
        }

        .gmack-logo-card {
            display: inline-flex;
            background: white;
            border-radius: 10px;
            padding: 0.5rem 0.9rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        footer p {
            font-size: 0.85rem;
            opacity: 0.75;
        }

        @media (max-width: 768px) {
            nav .nav-links {
                gap: 1rem;
                font-size: 0.85rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero .cta-buttons {
                gap: 0.5rem;
            }

            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 640px) {
            .nav-toggle { display: flex; }

            nav .container { position: relative; }

            nav .nav-links {
                display: none;
                position: absolute;
                top: calc(100% + 0.85rem);
                left: 0;
                right: 0;
                flex-direction: column;
                align-items: stretch;
                gap: 0.35rem;
                background: var(--surface);
                border-radius: 14px;
                border: 1px solid var(--border);
                box-shadow: 0 16px 32px rgba(20, 24, 28, 0.16);
                padding: 0.85rem;
            }

            nav .nav-links.open { display: flex; }

            nav .nav-links li { width: 100%; }
            nav a.nav-link { display: block; padding: 0.6rem 0.5rem; }
            nav .nav-links .btn-nav { display: block; text-align: center; width: 100%; }

            .hero .container { grid-template-columns: 1fr; }
        }

        /* ---- Acronym explainer ---- */
        .acronym-line {
            max-width: 760px;
            margin: 1.5rem auto 0;
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.92);
            line-height: 1.7;
        }
        .acronym-line b { color: var(--clr-yellow); font-weight: 800; }
        .acronym-line.footer-acronym { color: rgba(255,255,255,0.65); margin: 0; text-align: center; max-width: 640px; }
        .acronym-line.footer-acronym b { color: var(--clr-orange); }

        /* ---- Scroll reveal ---- */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }
        .reveal.in-view { opacity: 1; transform: translateY(0); }
        .reveal-stagger.in-view > * { animation: staggerIn 0.6s ease both; }
        .reveal-stagger.in-view > *:nth-child(1) { animation-delay: 0.05s; }
        .reveal-stagger.in-view > *:nth-child(2) { animation-delay: 0.14s; }
        .reveal-stagger.in-view > *:nth-child(3) { animation-delay: 0.23s; }
        .reveal-stagger.in-view > *:nth-child(4) { animation-delay: 0.32s; }
        .reveal-stagger.in-view > *:nth-child(5) { animation-delay: 0.41s; }
        .reveal-stagger.in-view > *:nth-child(6) { animation-delay: 0.5s; }
        .reveal-stagger.in-view > *:nth-child(7) { animation-delay: 0.59s; }
        @keyframes staggerIn {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ---- Hero two-column layout: copy + worker photo ---- */
        .hero-copy { max-width: 560px; }
        .hero-art {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: heroArtFloat 5s ease-in-out infinite;
        }
        .hero-art img {
            display: block;
            width: 340px;
            height: 460px;
            object-fit: cover;
            object-position: 10% 35%;
            border-radius: 22px;
            border: 4px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 22px 44px rgba(20, 24, 28, 0.32);
        }
        .hero-art-badge {
            position: absolute;
            left: -1rem;
            bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: #fff;
            color: var(--clr-charcoal, #1c2126);
            padding: 0.65rem 0.9rem;
            border-radius: 14px;
            box-shadow: 0 14px 28px rgba(20, 24, 28, 0.28);
            font-size: 0.78rem;
            line-height: 1.25;
        }
        .hero-art-badge-icon { font-size: 1.4rem; }
        .hero-art-badge-text b { display: block; font-size: 0.85rem; }
        @keyframes heroArtFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* The hero photo eats the whole first screen on a phone with
           nothing else visible below it — hide it there and let the
           headline and CTA lead instead. */
        @media (max-width: 640px) {
            .hero-art { display: none; }
            .hero .container { grid-template-columns: 1fr; }
        }

        /* ---- Hero animated backdrop shimmer ---- */
        .hero { background-size: 200% 200%; animation: heroShimmer 12s ease infinite; }
        @keyframes heroShimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ---- Multi-business showcase ---- */
        .multibiz { padding: 4.5rem 2rem; background: var(--surface); }
        .multibiz .container { max-width: 1200px; }
        .multibiz-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.75rem; margin-bottom: 2.5rem; }
        .biz-card {
            background: var(--surface-alt);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.75rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .biz-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px var(--shadow-brand); }
        .biz-card .biz-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin-bottom: 1rem;
            background: var(--gradient-brand); color: white;
            box-shadow: 0 8px 18px var(--shadow-brand);
        }
        .biz-card h3 { color: var(--ink); margin-bottom: 0.5rem; }
        .biz-card p { color: var(--muted); font-size: 0.92rem; }

        .biz-mock { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }
        .biz-mock-item {
            flex: 1 1 200px;
            max-width: 240px;
            background: var(--ink);
            color: white;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .biz-mock-item::before {
            content: '';
            position: absolute; inset: 0;
            background: var(--gradient-brand);
            opacity: 0.12;
        }
        .biz-mock-item .biz-mock-name { font-weight: 800; font-size: 0.95rem; position: relative; }
        .biz-mock-item .biz-mock-tag { font-size: 0.72rem; opacity: 0.7; margin-top: 0.2rem; position: relative; }
        .biz-mock-item .biz-mock-bar { height: 5px; border-radius: 999px; background: rgba(255,255,255,0.15); margin-top: 0.85rem; overflow: hidden; position: relative; }
        .biz-mock-item .biz-mock-bar span { display: block; height: 100%; background: var(--gradient-brand); width: 0%; transition: width 1.4s ease; }
        .biz-mock-item.in-view .biz-mock-bar span { width: var(--pct, 60%); }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <?= view('components/logo', ['size' => 'sm']) ?>
            <ul class="nav-links" id="navLinks">
                <li><a class="nav-link" href="#features">Features</a></li>
                <li><a class="nav-link" href="#quickstart">Quick Start</a></li>
                <li><a class="nav-link" href="#install">📲 Get the App</a></li>
                <li><a class="btn btn-nav btn-nav-outline" href="<?= base_url('auth') ?>">Login</a></li>
                <li><a class="btn btn-nav btn-primary" href="<?= base_url('auth/register') ?>">Register</a></li>
            </ul>
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="navLinks">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-copy">
                <span class="eyebrow reveal">🏗️ Multi-Business POS for Hardware &amp; Construction Supply</span>
                <h1 class="reveal">One Smart POS.<br>Any Construction Supply Business.</h1>
                <p class="reveal">From independent hardware stores to multi-location construction suppliers — any business in the industry can sign up and get its own isolated inventory, cashiers, and sales analytics, built specifically for construction &amp; hardware supply.</p>

                <div class="cta-buttons reveal">
                    <a href="<?= base_url('auth/register') ?>" class="btn btn-primary">Register Your Business</a>
                    <a href="#features" class="btn btn-secondary">Learn More</a>
                </div>

                <p class="acronym-line reveal">
                    <b>Gmack</b> — <b>S</b>mart <b>D</b>ata-Driven <b>P</b>OS <b>S</b>ystem Integrated with a <b>S</b>ales <b>A</b>nalytics <b>D</b>ashboard, for Construction Supply.
                </p>
            </div>

            <div class="hero-art reveal">
                <img src="<?= base_url('assets/images/hero-tools.jpg') ?>" alt="Hardware and construction tools — saw, tape measure, level, and trowel" width="1600" height="1066" loading="eager">
                <div class="hero-art-badge">
                    <span class="hero-art-badge-icon">🔧</span>
                    <span class="hero-art-badge-text"><b>Live Stock</b><br>Tracked in real time</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Multi-Business Showcase -->
    <section class="multibiz" id="multibiz">
        <div class="container">
            <div class="section-heading reveal">
                <h2>One Platform, <span class="gradient-text">Every Business</span> in the Industry</h2>
                <p>Any construction or hardware supply business can sign up and get its own private workspace on the same platform — fully independent, fully isolated from every other business that joins.</p>
            </div>
            <div class="multibiz-grid reveal-stagger">
                <div class="biz-card">
                    <div class="biz-icon">🏢</div>
                    <h3>Unlimited Businesses</h3>
                    <p>Self-service registration spins up a brand-new, ready-to-use POS workspace for any hardware or construction supply business in minutes.</p>
                </div>
                <div class="biz-card">
                    <div class="biz-icon">🔒</div>
                    <h3>Fully Isolated Data</h3>
                    <p>Inventory, users, sales, and settings never cross business lines — each workspace only ever sees its own data.</p>
                </div>
                <div class="biz-card">
                    <div class="biz-icon">📈</div>
                    <h3>Per-Business Analytics</h3>
                    <p>Every business gets its own real-time sales analytics dashboard, so growth is measured against its own numbers.</p>
                </div>
            </div>

            <p style="text-align:center; color:var(--muted); font-size:0.85rem; margin-bottom:1.25rem;" class="reveal">A few of the unrelated, independent businesses already running on Gmack</p>
            <div class="biz-mock reveal-stagger">
                <div class="biz-mock-item" style="--pct:78%;">
                    <div class="biz-mock-name">🧱 Hardware &amp; Co.</div>
                    <div class="biz-mock-tag">142 items · 3 cashiers</div>
                    <div class="biz-mock-bar"><span></span></div>
                </div>
                <div class="biz-mock-item" style="--pct:55%;">
                    <div class="biz-mock-name">🏗️ BuildRight Supply</div>
                    <div class="biz-mock-tag">89 items · 2 cashiers</div>
                    <div class="biz-mock-bar"><span></span></div>
                </div>
                <div class="biz-mock-item" style="--pct:92%;">
                    <div class="biz-mock-name">🔧 Ace Tools Depot</div>
                    <div class="biz-mock-tag">216 items · 5 cashiers</div>
                    <div class="biz-mock-bar"><span></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Showcase Section -->
    <section class="categories" id="categories">
        <div class="container">
            <div class="section-heading reveal">
                <h2>Built for <span class="gradient-text">Hardware &amp; Construction</span></h2>
                <p>The kinds of inventory Gmack is purpose-built to handle — from hand tools to bulk materials.</p>
            </div>
            <div class="category-grid reveal-stagger">
                <div class="category-card">
                    <img src="<?= base_url('assets/images/promo/hammer.jpg') ?>" alt="Hammers & hand tools" loading="lazy">
                    <div class="category-overlay"><span class="category-label">Hammers &amp; Tools</span></div>
                </div>
                <div class="category-card">
                    <img src="<?= base_url('assets/images/promo/nails.jpg') ?>" alt="Nails & fasteners" loading="lazy">
                    <div class="category-overlay"><span class="category-label">Nails &amp; Fasteners</span></div>
                </div>
                <div class="category-card">
                    <img src="<?= base_url('assets/images/promo/pvc-pipe.jpg') ?>" alt="Pipes & plumbing supplies" loading="lazy">
                    <div class="category-overlay"><span class="category-label">Pipes &amp; Plumbing</span></div>
                </div>
                <div class="category-card">
                    <img src="<?= base_url('assets/images/promo/paint.jpg') ?>" alt="Paint & painting supplies" loading="lazy">
                    <div class="category-overlay"><span class="category-label">Paint &amp; Supplies</span></div>
                </div>
                <div class="category-card">
                    <img src="<?= base_url('assets/images/promo/lumber.jpg') ?>" alt="Lumber & wood" loading="lazy">
                    <div class="category-overlay"><span class="category-label">Lumber &amp; Wood</span></div>
                </div>
                <div class="category-card">
                    <img src="<?= base_url('assets/images/promo/wrench.jpg') ?>" alt="Wrenches & hand tools" loading="lazy">
                    <div class="category-overlay"><span class="category-label">Wrenches &amp; Hand Tools</span></div>
                </div>
            </div>
            <p class="category-credits">
                Representative category photos, not specific in-store products —
                see <a href="#" onclick="document.getElementById('promoCredits').style.display='block'; return false;">photo credits</a>.
            </p>
            <div id="promoCredits" style="display:none; text-align:center; font-size:0.72rem; color:var(--muted); margin-top:0.5rem;">
                "Hammer hand tool" &amp; "Wood planks" — public domain ·
                "NailsCloseup" &amp; "AdjustableWrenchWhiteBackground" — CC0, Wikimedia Commons ·
                "PVC Pipe" by Nikat — <a href="https://creativecommons.org/licenses/by-sa/3.0/" target="_blank" rel="noopener">CC BY-SA 3.0</a> ·
                "Tray, brush, roller, paint" by whereareyousimon — <a href="https://creativecommons.org/licenses/by/2.0/" target="_blank" rel="noopener">CC BY 2.0</a>, via Wikimedia Commons
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-heading reveal">
                <h2>Key <span class="gradient-text">Features</span></h2>
                <p>Everything a construction supply business needs to sell faster and stock smarter — whether you're an independent hardware store or run several locations.</p>
            </div>
            <div class="features-grid reveal-stagger">
                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3>Multi-Business Ready</h3>
                    <p>Register as many businesses as you need — each gets its own fully isolated workspace on the same platform.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Fast Checkout</h3>
                    <p>Quick and efficient sales processing with bulk item support. Perfect for high-volume construction supply sales.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏗️</div>
                    <h3>Construction Inventory</h3>
                    <p>Specialized inventory management for tools, materials, and supplies with SKU tracking and low-stock alerts.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👷</div>
                    <h3>Team Management</h3>
                    <p>Control access with role-based permissions for owners, managers, and cashiers. Track sales by employee.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Multi-Payment Support</h3>
                    <p>Accept cash, e-wallets, and QR payments. Manage customer accounts and invoicing in Philippine Peso.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Sales Analytics Dashboard</h3>
                    <p>Data-driven reports on daily sales, product performance, and inventory trends for smarter decisions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Secure &amp; Reliable</h3>
                    <p>Enterprise-grade security with encrypted data, secure authentication, and per-business data isolation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container reveal-stagger">
            <div class="stat-item">
                <h3>Unlimited</h3>
                <p>Businesses Supported</p>
            </div>
            <div class="stat-item">
                <h3>500+</h3>
                <p>SKUs Managed / Business</p>
            </div>
            <div class="stat-item">
                <h3>24/7</h3>
                <p>System Uptime</p>
            </div>
            <div class="stat-item">
                <h3>99.8%</h3>
                <p>Data Accuracy</p>
            </div>
        </div>
    </section>

    <!-- Quick Start Section -->
    <section class="quick-start" id="quickstart">
        <div class="container">
            <div class="section-heading reveal">
                <h2>Quick <span class="gradient-text">Start Guide</span></h2>
                <p>From registering your business to your first sale in four simple steps.</p>
            </div>
            <div class="steps reveal-stagger">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Register Your Business</h3>
                    <p>Sign up and get your own isolated workspace instantly. Create cashier accounts for your team members.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Stock Items</h3>
                    <p>Add your construction supplies to inventory. Set prices, quantities, and supplier information.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Start Selling</h3>
                    <p>Process customer sales instantly. Apply bulk discounts and manage payment methods efficiently.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Monitor Business</h3>
                    <p>Review daily sales, track inventory levels, and identify top-selling products with analytics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Install App -->
    <section class="install-app" id="install">
        <div class="container">
            <div class="install-card reveal">
                <div class="install-icon" style="display:flex;align-items:center;justify-content:center;background:var(--gradient-brand);">
                    <span style="font-size:2.2rem;">🏗️</span>
                </div>
                <h2>Get Gmack on Your Phone</h2>
                <p>Install it like an app — one tap from your home screen, full-screen, no app store needed.</p>

                <button type="button" id="installAppBtn" class="btn btn-primary" style="display:none;">
                    📲 Install App
                </button>

                <div id="installFallback">
                    <strong>To install:</strong> open this page in Chrome (Android) and tap the browser menu →
                    <strong>"Install app"</strong>. On iPhone/iPad, open it in Safari, tap the Share icon, then
                    <strong>"Add to Home Screen"</strong>.
                </div>

                <p id="installedMsg">✅ Already installed — check your home screen!</p>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta">
        <div class="inner reveal">
            <h2>Ready to run your business on Gmack?</h2>
            <p>Set up your own isolated workspace in minutes — no credit card required.</p>
            <a href="<?= base_url('auth/register') ?>" class="btn btn-secondary" style="background:white;color:var(--clr-magenta);border:none;">Create Free Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <?= view('components/logo', ['size' => 'sm', 'link' => false]) ?>
            <p class="acronym-line footer-acronym">
                <b>Gmack</b> stands for <b>S</b>mart <b>D</b>ata-Driven <b>P</b>OS <b>S</b>ystem Integrated with a <b>S</b>ales <b>A</b>nalytics <b>D</b>ashboard, for Construction Supply — one platform, unlimited businesses.
            </p>
            <p>&copy; <?= date('Y') ?> Gmack. All rights reserved. | Built with CodeIgniter 4</p>
        </div>
    </footer>

    <script>
        // Mobile nav toggle: open/close the dropdown menu, close it again on
        // link tap or an outside click so it never gets left open.
        (function () {
            var toggle = document.getElementById('navToggle');
            var links = document.getElementById('navLinks');
            if (!toggle || !links) return;

            function setOpen(open) {
                links.classList.toggle('open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            toggle.addEventListener('click', function () {
                setOpen(!links.classList.contains('open'));
            });

            links.addEventListener('click', function (e) {
                if (e.target.closest('a')) setOpen(false);
            });

            document.addEventListener('click', function (e) {
                if (!links.classList.contains('open')) return;
                if (!links.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                    setOpen(false);
                }
            });
        })();

        // Scroll-reveal: fade/slide elements in the first time they enter the viewport.
        (function () {
            var targets = document.querySelectorAll('.reveal, .reveal-stagger, .biz-mock-item');
            if (!('IntersectionObserver' in window) || !targets.length) {
                targets.forEach(function (el) { el.classList.add('in-view'); });
                return;
            }
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
            targets.forEach(function (el) { observer.observe(el); });
        })();

        // Register the service worker (required for the browser to treat this as installable).
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?= base_url('sw.js') ?>').catch(function (err) {
                    console.warn('Service worker registration failed:', err);
                });
            });
        }

        (function () {
            var installBtn = document.getElementById('installAppBtn');
            var fallback = document.getElementById('installFallback');
            var installedMsg = document.getElementById('installedMsg');
            var deferredPrompt = null;

            var isStandalone = window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true;

            if (isStandalone) {
                installedMsg.style.display = 'block';
                return;
            }

            // Chrome/Edge/Android fire this when the site qualifies as installable —
            // capture it so we can trigger the native install prompt from our own button.
            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                deferredPrompt = e;
                fallback.style.display = 'none';
                installBtn.style.display = 'inline-block';
            });

            installBtn.addEventListener('click', function () {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                deferredPrompt.userChoice.finally(function () {
                    deferredPrompt = null;
                    installBtn.style.display = 'none';
                });
            });

            window.addEventListener('appinstalled', function () {
                installBtn.style.display = 'none';
                fallback.style.display = 'none';
                installedMsg.style.display = 'block';
            });

            // Browsers that never fire beforeinstallprompt (Safari/iOS, Firefox) —
            // show manual "Add to Home Screen" instructions instead.
            setTimeout(function () {
                if (!deferredPrompt) {
                    fallback.style.display = 'block';
                }
            }, 1500);
        })();
    </script>
</body>
</html>
