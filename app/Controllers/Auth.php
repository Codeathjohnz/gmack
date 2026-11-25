<?php

namespace App\Controllers;

use App\Models\BusinessModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $businessModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->businessModel = new BusinessModel();
    }

    public function index()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/items');
        }
        return view('auth/login');
    }

    /**
     * Self-service sign-up: every new registrant creates a brand-new
     * business (tenant) and becomes its admin/owner.
     */
    public function register()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/items');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $businessName = trim((string) $this->request->getPost('business_name'));
            $username = trim((string) $this->request->getPost('username'));
            $email = trim((string) $this->request->getPost('email'));
            $password = $this->request->getPost('password');
            $confirmPassword = $this->request->getPost('confirm_password');

            log_message('info', 'Register attempt - Business: ' . $businessName . ', Username: ' . $username . ', Email: ' . $email);

            if (strlen($businessName) < 2) {
                session()->setFlashdata('error', 'Please enter your business/company name');
                return redirect()->back()->withInput();
            }

            if ($password !== $confirmPassword) {
                session()->setFlashdata('error', 'Passwords do not match');
                return redirect()->back()->withInput();
            }

            if (strlen($password) < 6) {
                session()->setFlashdata('error', 'Password must be at least 6 characters');
                return redirect()->back()->withInput();
            }

            if (strlen($username) < 3) {
                session()->setFlashdata('error', 'Username must be at least 3 characters');
                return redirect()->back()->withInput();
            }

            if ($this->userModel->where('username', $username)->first()) {
                session()->setFlashdata('error', 'Username already exists');
                return redirect()->back()->withInput();
            }

            if ($this->userModel->where('email', $email)->first()) {
                session()->setFlashdata('error', 'Email already exists');
                return redirect()->back()->withInput();
            }

            try {
                $slug = $this->businessModel->generateUniqueSlug($businessName);

                $businessId = $this->businessModel->insert([
                    'name'     => $businessName,
                    'slug'     => $slug,
                    'industry' => 'Hardware & Construction Supply',
                    'status'   => 'active',
                ], true);

                $userId = $this->userModel->insert([
                    'business_id' => $businessId,
                    'username'    => $username,
                    'email'       => $email,
                    'password'    => password_hash($password, PASSWORD_DEFAULT),
                    'role'        => 'admin',
                    'status'      => 'active',
                    'created_at'  => date('Y-m-d H:i:s'),
                ], true);

                $this->businessModel->update($businessId, ['owner_user_id' => $userId]);

                // Not logged in yet, so bypass the session-based logAudit() helper.
                (new \App\Models\AuditLogModel())->insert([
                    'business_id' => $businessId,
                    'user_id'     => $userId,
                    'action'      => 'business.registered',
                    'description' => "Self-registered business \"{$businessName}\" with admin \"{$username}\"",
                    'ip_address'  => $this->request->getIPAddress(),
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);

                log_message('info', "✅ New business registered: {$businessName} (#{$businessId}), owner {$username} (#{$userId})");
                session()->setFlashdata('success', '✅ Business registered! You can now sign in with your new account.');
                return redirect()->to('/auth');
            } catch (\Throwable $e) {
                log_message('error', 'REGISTER EXCEPTION: ' . $e->getMessage());
                session()->setFlashdata('error', '❌ System Error: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        return view('auth/register');
    }

    public function login()
    {
        // Debug: Log all POST data
        log_message('info', '=== LOGIN REQUEST ===');
        log_message('info', 'Method: ' . $this->request->getMethod());
        log_message('info', 'All POST data: ' . json_encode($_POST));
        
        if (strtolower($this->request->getMethod()) === 'post') {
            $credential = trim($this->request->getPost('username'));
            $password = $this->request->getPost('password');

            log_message('info', '=== LOGIN ATTEMPT ===');
            log_message('info', 'Credential (username/email): ' . $credential);
            log_message('info', 'Password length: ' . strlen($password));

            $user = $this->userModel->getUserByUsernameOrEmail($credential);

            if ($user) {
                log_message('info', 'User found: ' . $user['username'] . ' (' . $user['email'] . ')');
                log_message('info', 'User status: ' . $user['status']);
                log_message('info', 'Stored hash: ' . substr($user['password'], 0, 20) . '...');
                $verify_result = password_verify($password, $user['password']);
                log_message('info', 'Password verify result: ' . ($verify_result ? 'TRUE' : 'FALSE'));
                log_message('info', 'Password matches: ' . ($verify_result ? 'YES' : 'NO'));
            } else {
                log_message('info', 'User not found with credential: ' . $credential);
            }

            if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
                // Platform-level superadmin isn't tied to any one business.
                if ($user['role'] === 'superadmin') {
                    session()->set([
                        'user_id'  => $user['id'],
                        'username' => $user['username'],
                        'role'     => $user['role'],
                    ]);
                    log_message('info', 'Superadmin login: ' . $user['username']);
                    $this->logAudit('auth.login', 'Superadmin "' . $user['username'] . '" logged in');
                    return redirect()->to('/admin/businesses');
                }

                // Every admin/cashier belongs to exactly one business — block
                // login if that business has been deactivated by platform staff.
                $business = $user['business_id'] ? $this->businessModel->find($user['business_id']) : null;

                if (!$business || $business['status'] !== 'active') {
                    log_message('info', 'Login blocked — business inactive or missing for user: ' . $user['username']);
                    session()->setFlashdata('error', 'This business account has been deactivated. Please contact support.');
                    return redirect()->back();
                }

                log_message('info', 'Login successful for: ' . $user['username']);
                session()->set([
                    'user_id'       => $user['id'],
                    'username'      => $user['username'],
                    'role'          => $user['role'],
                    'business_id'   => $business['id'],
                    'business_name' => $business['name'],
                ]);
                log_message('info', 'Session set. Redirecting based on role: ' . $user['role']);
                $this->logAudit('auth.login', ucfirst($user['role']) . ' "' . $user['username'] . '" logged in');

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    log_message('info', 'Redirecting to /admin/dashboard');
                    return redirect()->to('/admin/dashboard');
                } else {
                    log_message('info', 'Redirecting to /cashier/sales');
                    return redirect()->to('/cashier/sales');
                }
            } else {
                log_message('info', 'Login failed for credential: ' . $credential);
                session()->setFlashdata('error', 'Invalid username/email or password');
                return redirect()->back();
            }
        }

        return view('auth/login');
    }

    public function logout()
    {
        if (session()->get('user_id')) {
            $this->logAudit('auth.logout', 'User "' . session()->get('username') . '" logged out');
        }
        session()->destroy();
        return redirect()->to('/auth');
    }
}
