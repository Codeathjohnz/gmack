<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['users'] = $this->userModel->forBusiness($this->currentBusinessId())->findAll();
        return view('admin/users/list', $data);
    }

    public function create()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $role = $this->request->getPost('role');
            $data = [
                'business_id' => $this->currentBusinessId(),
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role' => in_array($role, ['admin', 'cashier'], true) ? $role : 'cashier',
                'status' => $this->request->getPost('status'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            log_message('info', '=== USER CREATION ATTEMPT ===');
            log_message('info', 'Username: ' . $data['username']);
            log_message('info', 'Email: ' . $data['email']);
            log_message('info', 'Role: ' . $data['role']);

            if ($this->userModel->save($data)) {
                log_message('info', 'User created successfully: ' . $data['username']);
                $this->logAudit('user.created', 'Created ' . $data['role'] . ' account "' . $data['username'] . '"');
                session()->setFlashdata('success', 'User created successfully');
                return redirect()->to('/admin/users');
            } else {
                $errors = $this->userModel->errors();
                log_message('error', 'User creation failed. Errors: ' . json_encode($errors));
                session()->setFlashdata('error', 'Failed to create user: ' . implode(', ', $errors ?? ['Unknown error']));
                return redirect()->back()->withInput();
            }
        }

        return view('admin/users/create');
    }

    public function edit($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['user'] = $this->userModel->forBusiness($this->currentBusinessId())->find($id);

        if (!$data['user']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $role = $this->request->getPost('role');
            $updateData = [
                'id' => $id,
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'role' => in_array($role, ['admin', 'cashier'], true) ? $role : 'cashier',
                'status' => $this->request->getPost('status'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Only update password if provided
            if ($this->request->getPost('password')) {
                $updateData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
            }

            log_message('info', '=== USER UPDATE ATTEMPT ===');
            log_message('info', 'User ID: ' . $id);
            log_message('info', 'Username: ' . $updateData['username']);
            log_message('info', 'Email: ' . $updateData['email']);
            log_message('info', 'Role: ' . $updateData['role']);

            if ($this->userModel->save($updateData)) {
                log_message('info', 'User updated successfully: ID ' . $id);
                $this->logAudit('user.updated', 'Updated account "' . $updateData['username'] . '"');
                session()->setFlashdata('success', 'User updated successfully');
                return redirect()->to('/admin/users');
            } else {
                $errors = $this->userModel->errors();
                log_message('error', 'User update failed. Errors: ' . json_encode($errors));
                session()->setFlashdata('error', 'Failed to update user: ' . implode(', ', $errors ?? ['Unknown error']));
                return redirect()->back()->withInput();
            }
        }

        return view('admin/users/edit', $data);
    }

    public function delete($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $existing = $this->userModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$existing) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        if ($this->userModel->delete($id)) {
            $this->logAudit('user.deleted', 'Deleted account "' . $existing['username'] . '"');
            session()->setFlashdata('success', 'User deleted successfully');
        } else {
            session()->setFlashdata('error', 'Failed to delete user');
        }

        return redirect()->to('/admin/users');
    }
}
