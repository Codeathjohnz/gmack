<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * Public marketing landing page for the Gmack platform. It no longer
     * shows any single tenant's live inventory — this is a multi-business
     * enterprise platform now, not one store's storefront.
     */
    public function index(): string
    {
        return view('landing');
    }

    /**
     * Landing point for a masked address (/v/<code>). The browser tab knows
     * which real page the code stands for and forwards there; any other
     * visitor is sent to the dashboard, which asks them to log in.
     */
    public function masked(string $code = ''): string
    {
        $code = preg_replace('/[^A-Za-z0-9]/', '', $code);
        return view('components/url_resolve', ['code' => substr($code, 0, 32)]);
    }
}
