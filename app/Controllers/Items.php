<?php

namespace App\Controllers;

use App\Models\ItemModel;

class Items extends BaseController
{
    protected $itemModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
    }

    protected function guard()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data['items'] = $this->itemModel->forBusiness($this->currentBusinessId())->findAll();
        return view('items/index', $data);
    }

    public function add()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'business_id' => $this->currentBusinessId(),
                'code' => $this->request->getPost('code'),
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'price' => $this->request->getPost('price'),
                'quantity' => $this->request->getPost('quantity'),
                'status' => $this->request->getPost('status') ?? 'active',
                'created_by' => session()->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->itemModel->save($data)) {
                session()->setFlashdata('success', 'Item added successfully!');
                return redirect()->to('/items');
            } else {
                session()->setFlashdata('error', 'Failed to add item: ' . implode(', ', $this->itemModel->errors()));
                return redirect()->back();
            }
        }

        return view('items/add');
    }

    public function edit($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data['item'] = $this->itemModel->forBusiness($this->currentBusinessId())->find($id);

        if (!$data['item']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        if ($this->request->getMethod() === 'POST') {
            $updateData = [
                'id' => $id,
                'code' => $this->request->getPost('code'),
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'price' => $this->request->getPost('price'),
                'quantity' => $this->request->getPost('quantity'),
                'status' => $this->request->getPost('status'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->itemModel->save($updateData)) {
                session()->setFlashdata('success', 'Item updated successfully!');
                return redirect()->to('/items');
            } else {
                session()->setFlashdata('error', 'Failed to update item');
                return redirect()->back();
            }
        }

        return view('items/edit', $data);
    }

    public function delete($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $item = $this->itemModel->forBusiness($this->currentBusinessId())->find($id);

        if (!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }

        if ($this->itemModel->delete($id)) {
            session()->setFlashdata('success', 'Item deleted successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to delete item');
        }

        return redirect()->to('/items');
    }
}
