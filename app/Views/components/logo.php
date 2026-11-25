<?php
/**
 * Gmack — text wordmark component (no image asset).
 *
 * SMART DATA-DRIVEN POS SYSTEM INTEGRATED WITH A SALES ANALYTICS DASHBOARD
 * FOR CONSTRUCTION SUPPLY — shortened everywhere to the "Gmack" mark.
 *
 * Usage:
 *   <?= view('components/logo') ?>
 *   <?= view('components/logo', ['size' => 'lg', 'tagline' => true]) ?>
 *   <?= view('components/logo', ['size' => '2rem', 'link' => false, 'class' => 'my-logo']) ?>
 *
 * Params:
 *   size     'xs'|'sm'|'md'|'lg'|'xl' or a raw CSS font-size. Default 'md'.
 *   link     bool — wrap the mark in a link to the site home. Default true.
 *   href     string — override the link target. Default base_url('/').
 *   tagline  bool — show the "Construction Supply POS" caption line. Default false.
 *   class    string — extra class(es) on the wrapper.
 */

$size    = $size ?? 'md';
$link    = $link ?? true;
$href    = $href ?? base_url('/');
$tagline = $tagline ?? false;
$class   = $class ?? '';

$presetSizes = [
    'xs' => '0.95rem',
    'sm' => '1.15rem',
    'md' => '1.5rem',
    'lg' => '2.1rem',
    'xl' => '3.1rem',
];
$fontSize = $presetSizes[$size] ?? $size;

// A hard hat — two-tone (dome in safety orange, brim in caution yellow) so
// it reads clearly as construction/hardware safety gear even at small sizes,
// and matches the favicon's coloring for a consistent mark everywhere.
$icon = '<svg class="brand-mark-icon" viewBox="0 0 24 24" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    . '<path d="M4 15c0-5 3.6-9 8-9s8 4 8 9" fill="none" stroke="#ff6b00" stroke-width="2" stroke-linecap="round"/>'
    . '<path d="M12 6v2.6" stroke="#ff6b00" stroke-width="2" stroke-linecap="round"/>'
    . '<rect x="2" y="15" width="20" height="3.2" rx="1.6" fill="#ffc400"/>'
    . '</svg>';

$wordmark = '<span class="brand-mark ' . esc($class, 'attr') . '" style="font-size:' . esc($fontSize, 'attr') . ';">'
    . $icon
    . '<span class="brand-mark-text brand-mark-metal">Gmack</span>'
    . '</span>'
    . ($tagline ? '<span class="brand-mark-tagline">Construction Supply POS &amp; Analytics</span>' : '');

if ($link) {
    printf(
        '<a href="%s" class="brand-mark-link" aria-label="Gmack — Home">%s</a>',
        esc($href, 'attr'),
        $wordmark
    );
} else {
    echo $wordmark;
}
