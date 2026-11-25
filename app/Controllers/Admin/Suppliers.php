<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SupplierModel;

class Suppliers extends BaseController
{
    protected $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    protected function guard()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data['suppliers'] = $this->supplierModel->forBusiness($this->currentBusinessId())->orderBy('name', 'ASC')->findAll();
        return view('admin/suppliers/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'business_id'    => $this->currentBusinessId(),
                'name'           => $this->request->getPost('name'),
                'contact_person' => $this->request->getPost('contact_person'),
                'phone'          => $this->request->getPost('phone'),
                'email'          => $this->request->getPost('email'),
                'address'        => $this->request->getPost('address'),
                'status'         => $this->request->getPost('status') ?: 'active',
                'created_at'     => date('Y-m-d H:i:s'),
            ];

            $id = $this->supplierModel->insert($data, true);
            $this->logAudit('supplier.created', 'Added supplier "' . $data['name'] . '"');
            session()->setFlashdata('success', 'Supplier added successfully');
            return redirect()->to('/admin/suppliers');
        }

        return view('admin/suppliers/create');
    }

    public function edit($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data['supplier'] = $this->supplierModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$data['supplier']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Supplier not found');
        }

        if ($this->request->getMethod() === 'POST') {
            $updateData = [
                'name'           => $this->request->getPost('name'),
                'contact_person' => $this->request->getPost('contact_person'),
                'phone'          => $this->request->getPost('phone'),
                'email'          => $this->request->getPost('email'),
                'address'        => $this->request->getPost('address'),
                'status'         => $this->request->getPost('status'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            $this->supplierModel->update($id, $updateData);
            $this->logAudit('supplier.updated', 'Updated supplier "' . $updateData['name'] . '"');
            session()->setFlashdata('success', 'Supplier updated successfully');
            return redirect()->to('/admin/suppliers');
        }

        return view('admin/suppliers/edit', $data);
    }

    public function delete($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $supplier = $this->supplierModel->forBusiness($this->currentBusinessId())->find($id);
        if ($supplier) {
            $this->supplierModel->delete($id);
            $this->logAudit('supplier.deleted', 'Deleted supplier "' . $supplier['name'] . '"');
            session()->setFlashdata('success', 'Supplier deleted');
        }

        return redirect()->to('/admin/suppliers');
    }
}
