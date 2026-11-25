<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\UserModel;

class BIR extends BaseController
{
    protected $saleModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->saleModel = new SaleModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * BIR Compliance Dashboard - Public Access
     */
    public function index()
    {
        $businessId = $this->currentBusinessId();

        // Get date range from request
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        // POS System Information
        $business = $this->currentBusiness();
        $data['pos_system'] = [
            'name' => ($business['name'] ?? 'Gmack') . ' — Gmack',
            'version' => '1.0.0',
            'type' => 'Point of Sale (POS)',
            'location' => config('App')->baseURL,
            'installation_date' => '2026-04-05',
            'last_modified' => date('Y-m-d H:i:s'),
        ];

        // Daily Sales Summary
        $salesQuery = $this->saleModel
            ->forBusiness($businessId)
            ->where('DATE(created_at) >=', $dateFrom)
            ->where('DATE(created_at) <=', $dateTo)
            ->orderBy('created_at', 'DESC');

        $data['total_sales'] = $salesQuery->countAllResults();

        $salesData = $this->saleModel
            ->forBusiness($businessId)
            ->where('DATE(created_at) >=', $dateFrom)
            ->where('DATE(created_at) <=', $dateTo)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Calculate totals
        $data['total_amount'] = array_sum(array_column($salesData, 'total_amount'));
        $data['total_discount'] = array_sum(array_column($salesData, 'discount'));
        $data['total_net_amount'] = array_sum(array_column($salesData, 'net_amount'));

        // Sales by Payment Method
        $data['sales_by_method'] = [];
        foreach ($salesData as $sale) {
            $method = $sale['payment_method'];
            if (!isset($data['sales_by_method'][$method])) {
                $data['sales_by_method'][$method] = ['count' => 0, 'total' => 0];
            }
            $data['sales_by_method'][$method]['count']++;
            $data['sales_by_method'][$method]['total'] += $sale['net_amount'];
        }

        // Sales by Cashier
        $data['sales_by_cashier'] = [];
        foreach ($salesData as $sale) {
            $cashierId = $sale['cashier_id'];
            $cashier = $this->userModel->find($cashierId);
            $cashierName = $cashier['username'] ?? 'Unknown';
            
            if (!isset($data['sales_by_cashier'][$cashierName])) {
                $data['sales_by_cashier'][$cashierName] = ['count' => 0, 'total' => 0];
            }
            $data['sales_by_cashier'][$cashierName]['count']++;
            $data['sales_by_cashier'][$cashierName]['total'] += $sale['net_amount'];
        }

        // Get all transactions
        $data['transactions'] = $salesData;

        // Get registered users
        $data['registered_users'] = $this->userModel->forBusiness($businessId)->findAll();

        $data['date_from'] = $dateFrom;
        $data['date_to'] = $dateTo;

        return view('bir/monitoring', $data);
    }

    /**
     * Export BIR report as CSV for submission
     */
    public function exportCSV()
    {
        $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

        $sales = $this->saleModel
            ->forBusiness($this->currentBusinessId())
            ->where('DATE(created_at) >=', $dateFrom)
            ->where('DATE(created_at) <=', $dateTo)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Create CSV
        $filename = 'BIR_Report_' . $dateFrom . '_to_' . $dateTo . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, [
            'Transaction ID',
            'Reference No',
            'Date & Time',
            'Cashier',
            'Items Count',
            'Total Amount',
            'Discount',
            'Net Amount',
            'Payment Method',
            'Amount Received',
            'Change',
            'Status'
        ]);

        // CSV Data
        foreach ($sales as $sale) {
            $cashier = $this->userModel->find($sale['cashier_id']);
            fputcsv($output, [
                $sale['id'],
                $sale['reference_no'],
                $sale['created_at'],
                $cashier['username'] ?? 'Unknown',
                $this->getSaleItemCount($sale['id']),
                number_format($sale['total_amount'], 2),
                number_format($sale['discount'], 2),
                number_format($sale['net_amount'], 2),
                $sale['payment_method'],
                number_format($sale['amount_received'] ?? 0, 2),
                number_format($sale['change_amount'] ?? 0, 2),
                $sale['status']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get count of items in a sale
     */
    private function getSaleItemCount($saleId)
    {
        return $this->db->table('sale_items')
            ->where('sale_id', $saleId)
            ->countAllResults();
    }
}
