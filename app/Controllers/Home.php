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
}
