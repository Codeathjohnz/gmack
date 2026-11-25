<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class Categories extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['categories'] = $this->categoryModel->forBusiness($this->currentBusinessId())->orderBy('name', 'ASC')->findAll();
        return view('admin/categories/index', $data);
    }

    public function create()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'business_id' => $this->currentBusinessId(),
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'color'       => $this->request->getPost('color') ?: '#6c757d',
                'status'      => $this->request->getPost('status') ?: 'active',
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if ($this->categoryModel->save($data)) {
                $this->logAudit('category.created', 'Created category "' . $data['name'] . '"');
                session()->setFlashdata('success', 'Category created successfully');
                return redirect()->to('/admin/categories');
            } else {
                session()->setFlashdata('error', 'Failed to create category');
                return redirect()->back()->withInput();
            }
        }

        return view('admin/categories/create');
    }

    public function edit($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['category'] = $this->categoryModel->forBusiness($this->currentBusinessId())->find($id);

        if (!$data['category']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Category not found');
        }

        if ($this->request->getMethod() === 'POST') {
            $updateData = [
                'id'          => $id,
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'color'       => $this->request->getPost('color') ?: '#6c757d',
                'status'      => $this->request->getPost('status'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            if ($this->categoryModel->save($updateData)) {
                $this->logAudit('category.updated', 'Updated category "' . $updateData['name'] . '"');
                session()->setFlashdata('success', 'Category updated successfully');
                return redirect()->to('/admin/categories');
            } else {
                session()->setFlashdata('error', 'Failed to update category');
                return redirect()->back();
            }
        }

        return view('admin/categories/edit', $data);
    }

    public function delete($id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $existing = $this->categoryModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$existing) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Category not found');
        }

        // Check if category has items
        $itemModel = new \App\Models\ItemModel();
        $itemCount = $itemModel->where('category_id', $id)->countAllResults();

        if ($itemCount > 0) {
            session()->setFlashdata('error', 'Cannot delete category with ' . $itemCount . ' item(s) assigned. Reassign items first.');
            return redirect()->to('/admin/categories');
        }

        if ($this->categoryModel->delete($id)) {
            $this->logAudit('category.deleted', 'Deleted category "' . $existing['name'] . '"');
            session()->setFlashdata('success', 'Category deleted successfully');
        } else {
            session()->setFlashdata('error', 'Failed to delete category');
        }

        return redirect()->to('/admin/categories');
    }
}
