<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemModel;

class Barcodes extends BaseController
{
    protected $itemModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $data['items'] = $this->itemModel->forBusiness($this->currentBusinessId())->where('status', 'active')->findAll();
        return view('admin/barcodes/index', $data);
    }

    public function generate()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $itemIds = $this->request->getPost('item_ids');
        $quantity = (int) ($this->request->getPost('quantity') ?: 1);
        $barcodeFormat = $this->request->getPost('barcode_format') ?: 'CODE128';

        if (empty($itemIds)) {
            session()->setFlashdata('error', 'Please select at least one item');
            return redirect()->back();
        }

        $items = $this->itemModel->forBusiness($this->currentBusinessId())->whereIn('id', $itemIds)->findAll();

        $data = [
            'items' => $items,
            'quantity' => min($quantity, 50),
            'barcodeFormat' => $barcodeFormat,
        ];

        return view('admin/barcodes/print', $data);
    }
}
