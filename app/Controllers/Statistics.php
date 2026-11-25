<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\SaleModel;
use App\Models\SaleItemModel;
use DateTime;

class Statistics extends BaseController
{
    protected $itemModel;
    protected $saleModel;
    protected $saleItemModel;
    protected $db;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
        $this->saleModel = new SaleModel();
        $this->saleItemModel = new SaleItemModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth');
        }

        $businessId = $this->currentBusinessId();

        // Get filter parameters
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $paymentFilter = $this->request->getGet('payment_method');
        $cashierFilter = $this->request->getGet('cashier');
        $itemFilter = $this->request->getGet('item');
        $period = $this->request->getGet('period');

        // Quick period presets (week / month / year) compute the actual
        // date range server-side so the filter form always reflects what
        // was really applied.
        if (in_array($period, ['week', 'month', 'year'], true)) {
            $now = new DateTime();
            switch ($period) {
                case 'week':
                    $start = (clone $now)->modify('monday this week');
                    break;
                case 'month':
                    $start = (clone $now)->modify('first day of this month');
                    break;
                case 'year':
                default:
                    $start = (clone $now)->setDate((int) $now->format('Y'), 1, 1);
                    break;
            }
            $dateFrom = $start->format('Y-m-d');
            $dateTo = $now->format('Y-m-d');
        } else {
            $period = ($dateFrom || $dateTo) ? 'custom' : '';
        }

        // Get all items for inventory statistics
        $allItems = $this->itemModel->forBusiness($businessId)->findAll();
        
        // Calculate inventory statistics
        $totalItems = count($allItems);
        $totalValue = 0;
        $lowStockCount = 0;
        $expiredCount = 0;
        $expiringCount = 0;
        
        $today = date('Y-m-d');
        $itemsByCategory = [];
        $stockLevels = [];
        
        foreach ($allItems as $item) {
            $totalValue += $item['price'] * $item['quantity'];
            
            if ($item['quantity'] <= 10) {
                $lowStockCount++;
            }
            
            // Check expiration
            if ($item['expiration_date']) {
                $expireDate = new DateTime($item['expiration_date']);
                $todayDate = new DateTime($today);
                $interval = $todayDate->diff($expireDate);
                $daysUntilExpiry = $interval->format('%r%a');
                
                if ($daysUntilExpiry < 0) {
                    $expiredCount++;
                } elseif ($daysUntilExpiry <= 30) {
                    $expiringCount++;
                }
            }
            
            $stockLevels[] = [
                'code' => $item['code'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
            ];
        }
        
        // Get sales statistics (filtered by date if provided)
        if ($dateFrom && $dateTo) {
            $sales = $this->saleModel->getSalesForDateRange($businessId, $dateFrom, $dateTo);
        } elseif ($dateFrom) {
            $sales = $this->saleModel->forBusiness($businessId)->where('DATE(created_at) >=', $dateFrom)->findAll();
        } elseif ($dateTo) {
            $sales = $this->saleModel->forBusiness($businessId)->where('DATE(created_at) <=', $dateTo)->findAll();
        } else {
            $sales = $this->saleModel->forBusiness($businessId)->findAll();
        }

        // Filter by cashier if selected
        if ($cashierFilter) {
            $sales = array_filter($sales, function($sale) use ($cashierFilter) {
                $cashierInfo = $this->db->table('users')->where('id', $sale['cashier_id'])->first();
                return $cashierInfo && $cashierInfo->username === $cashierFilter;
            });
        }

        // Get list of all cashiers for filter dropdown
        $allCashiers = $this->db->table('users')
            ->where('business_id', $businessId)
            ->groupStart()
                ->where('role', 'cashier')
                ->orWhere('role', 'admin')
            ->groupEnd()
            ->orderBy('username', 'ASC')
            ->distinct()->select('id, username')
            ->get()->getResultArray();

        $totalSales = count($sales);
        $totalRevenue = 0;
        $monthlySalesData = [];
        
        foreach ($sales as $sale) {
            $totalRevenue += $sale['net_amount'];
            
            // Group by month
            $month = date('M Y', strtotime($sale['created_at']));
            if (!isset($monthlySalesData[$month])) {
                $monthlySalesData[$month] = 0;
            }
            $monthlySalesData[$month] += $sale['net_amount'];
        }

        // Get distinct payment methods used in sales
        $paymentMethods = array_unique(array_column($sales, 'payment_method'));
        sort($paymentMethods);

        // Get sold items for the filtered date range (every item sold, with total amount)
        $soldItems = $this->saleItemModel->getSoldItemsForDateRange($businessId, $dateFrom, $dateTo, $itemFilter);

        // Full list of items that have ever been sold, for the "Item" filter dropdown
        $soldItemOptions = $this->db->table('sale_items')
            ->select('items.code, items.name')
            ->join('items', 'sale_items.item_id = items.id')
            ->join('sales', 'sale_items.sale_id = sales.id')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed')
            ->groupBy('items.id')
            ->orderBy('items.name', 'ASC')
            ->get()
            ->getResultArray();

        // Get all sales with cashier info for the filtered range
        $salesWithCashier = $this->db->table('sales')
            ->select('sales.id, sales.reference_no, sales.total_amount, sales.net_amount, sales.payment_method, sales.created_at, users.username as cashier_name')
            ->join('users', 'sales.cashier_id = users.id', 'left')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed');

        if ($dateFrom) {
            $salesWithCashier->where('DATE(sales.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $salesWithCashier->where('DATE(sales.created_at) <=', $dateTo);
        }
        if ($cashierFilter) {
            $salesWithCashier->where('users.username', $cashierFilter);
        }
        
        $salesWithCashier = $salesWithCashier->orderBy('sales.created_at', 'DESC')->get()->getResultArray();

        // Get items sold by cashier
        $itemsByCashier = $this->db->table('sale_items')
            ->select('users.username as cashier_name, items.code, items.name as item_name, sales.payment_method, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_amount, sales.created_at')
            ->join('sales', 'sale_items.sale_id = sales.id')
            ->join('items', 'sale_items.item_id = items.id')
            ->join('users', 'sales.cashier_id = users.id', 'left')
            ->where('sales.business_id', $businessId)
            ->where('sales.status', 'completed');

        if ($dateFrom) {
            $itemsByCashier->where('DATE(sales.created_at) >=', $dateFrom);
        }
        if ($dateTo) {
            $itemsByCashier->where('DATE(sales.created_at) <=', $dateTo);
        }
        if ($cashierFilter) {
            $itemsByCashier->where('users.username', $cashierFilter);
        }
        if ($itemFilter) {
            $itemsByCashier->where('items.code', $itemFilter);
        }

        $itemsByCashier = $itemsByCashier->groupBy('users.id, items.id, sales.payment_method')
                                        ->orderBy('users.username', 'ASC')
                                        ->orderBy('sales.payment_method', 'ASC')
                                        ->orderBy('total_qty', 'DESC')
                                        ->get()
                                        ->getResultArray();

        // When a specific item is selected, also build a clean per-cashier
        // breakdown for just that item (who sold it, how much, for how much).
        $itemCashierBreakdown = [];
        $itemFilterDetails = null;
        if ($itemFilter) {
            $itemCashierQuery = $this->db->table('sale_items')
                ->select('users.username as cashier_name, items.code, items.name as item_name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_amount, COUNT(DISTINCT sales.id) as transaction_count')
                ->join('sales', 'sale_items.sale_id = sales.id')
                ->join('items', 'sale_items.item_id = items.id')
                ->join('users', 'sales.cashier_id = users.id', 'left')
                ->where('sales.business_id', $businessId)
                ->where('sales.status', 'completed')
                ->where('items.code', $itemFilter);

            if ($dateFrom) {
                $itemCashierQuery->where('DATE(sales.created_at) >=', $dateFrom);
            }
            if ($dateTo) {
                $itemCashierQuery->where('DATE(sales.created_at) <=', $dateTo);
            }
            if ($cashierFilter) {
                $itemCashierQuery->where('users.username', $cashierFilter);
            }

            $itemCashierBreakdown = $itemCashierQuery->groupBy('users.id, items.id')
                                                     ->orderBy('total_qty', 'DESC')
                                                     ->get()
                                                     ->getResultArray();

            $itemFilterDetails = $this->itemModel->forBusiness($businessId)->where('code', $itemFilter)->first();
        }

        $soldByPaymentRaw = $this->saleItemModel->getSoldItemsByPaymentMethod($businessId, $dateFrom, $dateTo);
        $soldByPayment = [];
        foreach ($soldByPaymentRaw as $row) {
            $method = $row['payment_method'] ?: 'Unknown';
            if (!isset($soldByPayment[$method])) {
                $soldByPayment[$method] = [];
            }
            $soldByPayment[$method][] = $row;
        }

        // Filter by payment method if selected
        if ($paymentFilter) {
            $soldByPayment = array_filter($soldByPayment, function($key) use ($paymentFilter) {
                return strtolower($key) === strtolower($paymentFilter);
            }, ARRAY_FILTER_USE_KEY);
        }
        
        // Prepare data for charts
        $data = [
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'lowStockCount' => $lowStockCount,
            'expiredCount' => $expiredCount,
            'expiringCount' => $expiringCount,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'stockLevels' => array_slice($stockLevels, 0, 10), // Top 10 items
            'monthlySalesData' => $monthlySalesData,
            'allItems' => $allItems,
            'soldItems' => $soldItems,
            'soldByPayment' => $soldByPayment,
            'paymentMethods' => $paymentMethods,
            'paymentFilter' => $paymentFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'period' => $period,
            'salesWithCashier' => $salesWithCashier,
            'allCashiers' => $allCashiers,
            'cashierFilter' => $cashierFilter,
            'itemsByCashier' => $itemsByCashier,
            'soldItemOptions' => $soldItemOptions,
            'itemFilter' => $itemFilter,
            'itemFilterDetails' => $itemFilterDetails,
            'itemCashierBreakdown' => $itemCashierBreakdown,
        ];
        
        return view('statistics', $data);
    }
}
