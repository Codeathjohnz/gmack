<?php

namespace App\Controllers\Cashier;

use App\Controllers\BaseController;
use App\Models\ItemModel;
use App\Models\SaleModel;
use App\Models\SaleItemModel;
use App\Models\PaymentMethodModel;
use App\Models\SettingModel;
use App\Models\StockMovementModel;
use App\Models\UserModel;

class Sales extends BaseController
{
    protected $itemModel;
    protected $saleModel;
    protected $saleItemModel;
    protected $userModel;
    protected $stockMovementModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
        $this->saleModel = new SaleModel();
        $this->saleItemModel = new SaleItemModel();
        $this->userModel = new UserModel();
        $this->stockMovementModel = new StockMovementModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || !in_array(session()->get('role'), ['cashier', 'admin'])) {
            return redirect()->to('/auth');
        }

        $businessId = $this->currentBusinessId();
        $data['items'] = $this->itemModel->getActiveItems($businessId);
        $paymentMethodModel = new PaymentMethodModel();
        $data['paymentMethods'] = $paymentMethodModel->getActiveMethods($businessId);

        $settingModel = new SettingModel();
        $data['alarmSound'] = $settingModel->getSetting($businessId, 'alarm_sound', 'classic');
        $customPath = $settingModel->getSetting($businessId, 'alarm_custom_path', '');
        $data['alarmCustomUrl'] = ($customPath !== '' && is_file(FCPATH . $customPath)) ? base_url($customPath) : null;

        return view('cashier/sales', $data);
    }

    public function processSale()
    {
        if (!session()->get('user_id') || !in_array(session()->get('role'), ['cashier', 'admin'])) {
            return redirect()->to('/auth');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $itemsJson = $this->request->getPost('items');
            $items = json_decode($itemsJson, true);
            
            if (empty($items)) {
                log_message('error', 'Sale failed: items empty. Raw JSON: ' . ($itemsJson ?? 'NULL'));
                session()->setFlashdata('error', 'No items received. Please try again.');
                return redirect()->back();
            }
            $totalAmount = (float) $this->request->getPost('total_amount');
            $discount = (float) $this->request->getPost('discount');
            $netAmount = (float) $this->request->getPost('net_amount');
            $paymentMethod = $this->request->getPost('payment_method');
            $paymentReference = $this->request->getPost('payment_reference');
            $amountReceived = (float) $this->request->getPost('amount_received');
            $changeAmount = (float) $this->request->getPost('change_amount');

            if ($amountReceived <= 0) {
                $amountReceived = $netAmount;
                $changeAmount = 0;
            }

            $businessId = $this->currentBusinessId();

            // Generate OR number from settings
            $settingModel = new SettingModel();
            $orPrefix = $settingModel->getSetting($businessId, 'or_prefix', 'OR-');
            $orNumber = (int) $settingModel->getSetting($businessId, 'or_next_number', '1');
            $referenceNo = $orPrefix . $orNumber;

            // Increment for next sale
            $settingModel->setSetting($businessId, 'or_next_number', (string) ($orNumber + 1));

            $saleData = [
                'business_id' => $businessId,
                'reference_no' => $referenceNo,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference ?: null,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'cashier_id' => session()->get('user_id'),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Save sale
            $saleId = $this->saleModel->insert($saleData);

            if ($saleId) {
                // Save sale items and update inventory
                foreach ($items as $item) {
                    $quantity = (int) $item['quantity'];
                    $price = (float) $item['price'];
                    $subtotal = $quantity * $price;

                    $saleItemData = [
                        'business_id' => $businessId,
                        'sale_id' => $saleId,
                        'item_id' => $item['item_id'],
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ];

                    $this->saleItemModel->insert($saleItemData);

                    // Update item quantity (only for items belonging to this business)
                    $currentItem = $this->itemModel->forBusiness($businessId)->find($item['item_id']);
                    if ($currentItem) {
                        $newQuantity = $currentItem['quantity'] - $quantity;
                        $this->itemModel->update($item['item_id'], ['quantity' => $newQuantity]);

                        $this->stockMovementModel->log(
                            $businessId,
                            (int) $item['item_id'],
                            'sale',
                            -$quantity,
                            null,
                            $referenceNo,
                            session()->get('user_id')
                        );
                    }
                }

                return redirect()->to('/cashier/sales/receipt/' . $saleId);
            } else {
                session()->setFlashdata('error', 'Failed to process sale');
                return redirect()->back();
            }
        }

        return redirect()->to('/cashier/sales');
    }

    public function receipt($saleId)
    {
        if (!session()->get('user_id') || !in_array(session()->get('role'), ['cashier', 'admin'])) {
            return redirect()->to('/auth');
        }

        $data['sale'] = $this->saleModel->forBusiness($this->currentBusinessId())->find($saleId);

        if (!$data['sale']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Sale not found');
        }

        $data['items'] = $this->saleItemModel->getSaleItems($saleId);
        $data['cashier'] = $this->userModel->find($data['sale']['cashier_id']);

        return view('cashier/receipt', $data);
    }

    public function getSaleHistory()
    {
        if (!session()->get('user_id') || !in_array(session()->get('role'), ['cashier', 'admin'])) {
            return redirect()->to('/auth');
        }

        $sales = $this->saleModel->forBusiness($this->currentBusinessId())->where('cashier_id', session()->get('user_id'))->findAll();
        $data['sales'] = $sales;

        return view('cashier/history', $data);
    }
}
