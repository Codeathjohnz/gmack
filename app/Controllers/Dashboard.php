<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth');
        }

        $role = session()->get('role');
        $username = session()->get('username');

        $data['role'] = $role;
        $data['username'] = $username;

        if ($role === 'superadmin') {
            $data['redirect_to'] = '/admin/businesses';
            $data['title'] = 'Businesses';
        } elseif ($role === 'admin') {
            $data['redirect_to'] = '/admin/dashboard';
            $data['title'] = 'Admin Dashboard';
        } elseif ($role === 'cashier') {
            $data['redirect_to'] = '/cashier/sales';
            $data['title'] = 'Point of Sale';
        } else {
            return redirect()->to('/auth');
        }

        return view('dashboard/redirect', $data);
    }
}
