<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentMethodModel;

class PaymentMethods extends BaseController
{
    protected $paymentMethodModel;

    public function __construct()
    {
        $this->paymentMethodModel = new PaymentMethodModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['methods'] = $this->paymentMethodModel->forBusiness($this->currentBusinessId())->findAll();
        return view('admin/payment_methods/index', $data);
    }

    public function create()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'business_id'    => $this->currentBusinessId(),
                'name'           => $this->request->getPost('name'),
                'type'           => $this->request->getPost('type') ?: 'ewallet',
                'account_name'   => $this->request->getPost('account_name'),
                'account_number' => $this->request->getPost('account_number'),
                'icon'           => $this->request->getPost('icon') ?: 'bi-wallet2',
                'status'         => $this->request->getPost('status') ?: 'active',
                'created_at'     => date('Y-m-d H:i:s'),
            ];

            // Handle QR code upload
            $qrFile = $this->request->getFile('qr_code');
            if ($qrFile && $qrFile->isValid() && !$qrFile->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/qrcodes';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $qrFile->getRandomName();
                $qrFile->move($uploadPath, $newName);
                $data['qr_code'] = 'uploads/qrcodes/' . $newName;
            }

            $this->paymentMethodModel->insert($data);
            $this->logAudit('payment_method.created', 'Added payment method "' . $data['name'] . '"');
            session()->setFlashdata('success', 'Payment method added successfully.');
            return redirect()->to('/admin/payment-methods');
        }

        return view('admin/payment_methods/create');
    }

    public function edit($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $method = $this->paymentMethodModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$method) {
            session()->setFlashdata('error', 'Payment method not found.');
            return redirect()->to('/admin/payment-methods');
        }

        if ($this->request->getMethod() === 'POST') {
            $updateData = [
                'name'           => $this->request->getPost('name'),
                'type'           => $this->request->getPost('type') ?: 'ewallet',
                'account_name'   => $this->request->getPost('account_name'),
                'account_number' => $this->request->getPost('account_number'),
                'icon'           => $this->request->getPost('icon') ?: 'bi-wallet2',
                'status'         => $this->request->getPost('status'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            // Handle QR code upload
            $qrFile = $this->request->getFile('qr_code');
            if ($qrFile && $qrFile->isValid() && !$qrFile->hasMoved()) {
                // Delete old QR code
                if (!empty($method['qr_code']) && file_exists(FCPATH . $method['qr_code'])) {
                    unlink(FCPATH . $method['qr_code']);
                }
                $uploadPath = FCPATH . 'uploads/qrcodes';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $qrFile->getRandomName();
                $qrFile->move($uploadPath, $newName);
                $updateData['qr_code'] = 'uploads/qrcodes/' . $newName;
            }

            $this->paymentMethodModel->update($id, $updateData);
            $this->logAudit('payment_method.updated', 'Updated payment method "' . $updateData['name'] . '"');
            session()->setFlashdata('success', 'Payment method updated successfully.');
            return redirect()->to('/admin/payment-methods');
        }

        $data['method'] = $method;
        return view('admin/payment_methods/edit', $data);
    }

    public function delete($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $method = $this->paymentMethodModel->forBusiness($this->currentBusinessId())->find($id);
        if ($method) {
            // Delete QR code file
            if (!empty($method['qr_code']) && file_exists(FCPATH . $method['qr_code'])) {
                unlink(FCPATH . $method['qr_code']);
            }
            $this->paymentMethodModel->delete($id);
            $this->logAudit('payment_method.deleted', 'Deleted payment method "' . $method['name'] . '"');
            session()->setFlashdata('success', 'Payment method deleted.');
        }

        return redirect()->to('/admin/payment-methods');
    }
}
