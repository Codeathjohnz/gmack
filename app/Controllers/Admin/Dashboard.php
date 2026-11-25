<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemModel;
use App\Models\SaleItemModel;
use App\Models\SaleModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $itemModel;
    protected $userModel;
    protected $saleModel;

    protected const LOW_STOCK_THRESHOLD = 10;

    public function __construct()
    {
        $this->itemModel = new ItemModel();
        $this->userModel = new UserModel();
        $this->saleModel = new SaleModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $businessId = $this->currentBusinessId();

        $data['business'] = $this->currentBusiness();
        $data['total_items'] = $this->itemModel->forBusiness($businessId)->countAllResults();
        $data['active_items'] = $this->itemModel->forBusiness($businessId)->where('status', 'active')->countAllResults();
        $data['total_users'] = $this->userModel->forBusiness($businessId)->countAllResults();

        // Last 7 days of net sales, oldest first, zero-filled for days with no sales.
        $salesByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $salesByDay[$day] = 0.0;
        }
        $recentSales = $this->saleModel->forBusiness($businessId)
            ->where('DATE(created_at) >=', date('Y-m-d', strtotime('-6 days')))
            ->where('status', 'completed')
            ->findAll();
        foreach ($recentSales as $sale) {
            $day = date('Y-m-d', strtotime($sale['created_at']));
            if (isset($salesByDay[$day])) {
                $salesByDay[$day] += (float) $sale['net_amount'];
            }
        }
        $data['sales_trend_labels'] = array_map(fn($d) => date('M j', strtotime($d)), array_keys($salesByDay));
        $data['sales_trend_values'] = array_values($salesByDay);

        // Stock health breakdown for the doughnut chart, reusing the same
        // threshold logic as Admin\Items::index(). Also builds the
        // notifications list (low/out-of-stock + expired/expiring items)
        // in the same pass so we don't loop over every item twice.
        $items = $this->itemModel->forBusiness($businessId)->findAll();
        $healthy = $critical = $low = $out = 0;
        $today = date('Y-m-d');
        $alerts = [];

        foreach ($items as $item) {
            if ($item['status'] !== 'active') {
                continue;
            }

            $threshold = (int) ($item['low_stock_threshold'] ?? self::LOW_STOCK_THRESHOLD);
            $critThreshold = (int) floor($threshold / 2);
            $qty = (float) $item['quantity'];

            if ($qty <= 0) {
                $out++;
                $alerts[] = ['severity' => 1, 'type' => 'out', 'item' => $item, 'message' => 'Out of stock'];
            } elseif ($qty <= $critThreshold) {
                $critical++;
                $alerts[] = ['severity' => 2, 'type' => 'critical', 'item' => $item, 'message' => "Critical: only {$qty} left"];
            } elseif ($qty <= $threshold) {
                $low++;
                $alerts[] = ['severity' => 4, 'type' => 'low', 'item' => $item, 'message' => "Low stock: {$qty} left"];
            } else {
                $healthy++;
            }

            if (!empty($item['expiration_date'])) {
                $daysUntilExpiry = (int) floor((strtotime($item['expiration_date']) - strtotime($today)) / 86400);
                if ($daysUntilExpiry < 0) {
                    $alerts[] = ['severity' => 0, 'type' => 'expired', 'item' => $item, 'message' => 'Expired ' . abs($daysUntilExpiry) . ' day(s) ago'];
                } elseif ($daysUntilExpiry <= 30) {
                    $alerts[] = ['severity' => 3, 'type' => 'expiring', 'item' => $item, 'message' => "Expires in {$daysUntilExpiry} day(s)"];
                }
            }
        }
        $data['stock_health'] = ['healthy' => $healthy, 'low' => $low, 'critical' => $critical, 'out' => $out];

        // Most urgent first (expired > out of stock > critical > expiring soon > low).
        usort($alerts, fn($a, $b) => $a['severity'] <=> $b['severity']);
        $data['alerts'] = array_slice($alerts, 0, 10);
        $data['alerts_total'] = count($alerts);

        // Top selling products, last 30 days — by quantity sold.
        $topProducts = (new SaleItemModel())->getSoldItemsForDateRange(
            $businessId,
            date('Y-m-d', strtotime('-29 days')),
            date('Y-m-d')
        );
        $data['top_products'] = array_slice($topProducts, 0, 5);

        // Least selling: every active item, including ones with zero sales in
        // the last 30 days, ordered from fewest units sold upward.
        $soldById = array_column($topProducts, null, 'item_id');
        $least = [];
        foreach ($this->itemModel->forBusiness($businessId)->where('status', 'active')->findAll() as $item) {
            $sold = $soldById[$item['id']] ?? null;
            $least[] = [
                'item_id'           => $item['id'],
                'code'              => $item['code'],
                'name'              => $item['name'],
                'total_qty'         => $sold ? (float) $sold['total_qty'] : 0,
                'total_amount'      => $sold ? (float) $sold['total_amount'] : 0,
                'transaction_count' => $sold ? (int) $sold['transaction_count'] : 0,
                'stock'             => (float) $item['quantity'],
            ];
        }
        // Fewest sold first; among ties, the item with the most stock sitting
        // on the shelf comes first (that is the money tied up in slow stock).
        usort($least, fn($a, $b) => [$a['total_qty'], $b['stock'], $a['name']] <=> [$b['total_qty'], $a['stock'], $b['name']]);
        $data['least_products'] = array_slice($least, 0, 5);
        $data['unsold_count']   = count(array_filter($least, fn($p) => $p['total_qty'] <= 0));

        return view('admin/dashboard', $data);
    }
}
