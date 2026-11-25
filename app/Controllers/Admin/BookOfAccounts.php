<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Book of Accounts and quarterly reports, built from data the system already
 * records: completed sales, expenses, and received purchase orders.
 *
 * Books (cash basis, the usual set for a small non-VAT business):
 *   - Cash Receipts Book:      daily sales totals with the OR number range
 *   - Cash Disbursements Book: every expense and every received purchase order
 *   - General Ledger:          totals per account for the period
 * Quarterly report: month-by-month summary for one calendar quarter.
 *
 * Tax figures are estimates to help prepare BIR forms; they must be reviewed
 * by an accountant before filing.
 */
class BookOfAccounts extends BaseController
{
    /** Percentage tax rate used for the estimate (NIRC Sec. 116, non-VAT). */
    public const PERCENTAGE_TAX_RATE = 0.03;

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

        [$dateFrom, $dateTo, $period] = $this->resolveRange();
        $book = $this->request->getGet('book');
        if (!in_array($book, ['receipts', 'disbursements', 'ledger'], true)) {
            $book = 'receipts';
        }

        $businessId = $this->currentBusinessId();
        $receipts = $this->cashReceipts($businessId, $dateFrom, $dateTo);
        $disbursements = $this->cashDisbursements($businessId, $dateFrom, $dateTo);
        $ledger = $this->generalLedger($receipts, $disbursements);

        return view('admin/books/index', [
            'book'          => $book,
            'period'        => $period,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'receipts'      => $receipts,
            'disbursements' => $disbursements,
            'ledger'        => $ledger,
            'business'      => $this->currentBusiness(),
        ]);
    }

    public function quarterly()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        [$year, $quarter] = $this->resolveQuarter();
        $report = $this->quarterReport($this->currentBusinessId(), $year, $quarter);

        // Year choices span the years that have records (plus the current year).
        $range = $this->dataRange($this->currentBusinessId());
        $firstYear = $range ? (int) substr($range[0], 0, 4) : (int) date('Y');
        $lastYear = max((int) date('Y'), $range ? (int) substr($range[1], 0, 4) : 0);

        return view('admin/books/quarterly', [
            'years'    => range($lastYear, min($firstYear, $year)),
            'year'     => $year,
            'quarter'  => $quarter,
            'report'   => $report,
            'business' => $this->currentBusiness(),
            'taxRate'  => self::PERCENTAGE_TAX_RATE,
        ]);
    }

    public function export()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $type = $this->request->getGet('type');
        $rows = [];

        if ($type === 'quarterly') {
            [$year, $quarter] = $this->resolveQuarter();
            $r = $this->quarterReport($businessId, $year, $quarter);
            $filename = "Quarterly_Report_{$year}_Q{$quarter}.csv";
            $rows[] = ['Month', 'Transactions', 'Gross Sales', 'Discounts', 'Net Sales', 'Purchases', 'Expenses', 'Net Income (cash basis)'];
            foreach ($r['months'] as $m) {
                $rows[] = [$m['label'], $m['transactions'], $this->money($m['gross']), $this->money($m['discount']), $this->money($m['net']), $this->money($m['purchases']), $this->money($m['expenses']), $this->money($m['income'])];
            }
            $t = $r['totals'];
            $rows[] = ['TOTAL', $t['transactions'], $this->money($t['gross']), $this->money($t['discount']), $this->money($t['net']), $this->money($t['purchases']), $this->money($t['expenses']), $this->money($t['income'])];
            $rows[] = [];
            $rows[] = ['Estimated percentage tax (' . (self::PERCENTAGE_TAX_RATE * 100) . '% of net sales)', '', '', '', $this->money($r['percentageTax'])];
            $rows[] = ['For reference only. Have an accountant review before filing BIR Form 2551Q / 1701Q.'];
        } else {
            [$dateFrom, $dateTo] = $this->resolveRange();
            $suffix = "{$dateFrom}_to_{$dateTo}";
            $receipts = $this->cashReceipts($businessId, $dateFrom, $dateTo);
            $disbursements = $this->cashDisbursements($businessId, $dateFrom, $dateTo);

            if ($type === 'disbursements') {
                $filename = "Cash_Disbursements_Book_{$suffix}.csv";
                $rows[] = ['Date', 'Reference', 'Payee / Particulars', 'Account', 'Amount'];
                foreach ($disbursements['rows'] as $d) {
                    $rows[] = [$d['date'], $d['reference'], $d['particulars'], $d['account'], $this->money($d['amount'])];
                }
                $rows[] = ['', '', '', 'TOTAL', $this->money($disbursements['total'])];
            } elseif ($type === 'ledger') {
                $filename = "General_Ledger_{$suffix}.csv";
                $rows[] = ['Account', 'Debit', 'Credit'];
                $ledger = $this->generalLedger($receipts, $disbursements);
                foreach ($ledger['accounts'] as $a) {
                    $rows[] = [$a['account'], $a['debit'] ? $this->money($a['debit']) : '', $a['credit'] ? $this->money($a['credit']) : ''];
                }
                $rows[] = ['TOTAL', $this->money($ledger['totalDebit']), $this->money($ledger['totalCredit'])];
            } else {
                $filename = "Cash_Receipts_Book_{$suffix}.csv";
                $rows[] = ['Date', 'OR From', 'OR To', 'Transactions', 'Gross Sales', 'Discounts', 'Net Sales (Cash Received)'];
                foreach ($receipts['rows'] as $r) {
                    $rows[] = [$r['date'], $r['or_from'], $r['or_to'], $r['transactions'], $this->money($r['gross']), $this->money($r['discount']), $this->money($r['net'])];
                }
                $rows[] = ['TOTAL', '', '', $receipts['transactions'], $this->money($receipts['gross']), $this->money($receipts['discount']), $this->money($receipts['net'])];
            }
        }

        $this->logAudit('books.exported', 'Exported ' . $filename);

        $fh = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($fh); // BOM so Excel shows ₱ correctly
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    // ------------------------------------------------------------------
    // Books
    // ------------------------------------------------------------------

    /** Daily summary of completed sales: the Cash Receipts Book. */
    protected function cashReceipts(int $businessId, string $from, string $to): array
    {
        $rows = db_connect()->table('sales')
            ->select("DATE(created_at) AS date, COUNT(*) AS transactions,
                      SUM(total_amount) AS gross, SUM(discount) AS discount, SUM(net_amount) AS net,
                      MIN(id) AS first_id, MAX(id) AS last_id")
            ->where('business_id', $businessId)
            ->where('status', 'completed')
            ->where('DATE(created_at) >=', $from)
            ->where('DATE(created_at) <=', $to)
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get()->getResultArray();

        $db = db_connect();
        $totals = ['transactions' => 0, 'gross' => 0.0, 'discount' => 0.0, 'net' => 0.0];
        foreach ($rows as &$r) {
            // OR numbers are text (prefix + number), so take them from the first/last sale of the day.
            $r['or_from'] = $db->table('sales')->select('reference_no')->where('id', $r['first_id'])->get()->getRow('reference_no');
            $r['or_to'] = $db->table('sales')->select('reference_no')->where('id', $r['last_id'])->get()->getRow('reference_no');
            foreach (['transactions', 'gross', 'discount', 'net'] as $k) {
                $r[$k] = $k === 'transactions' ? (int) $r[$k] : (float) $r[$k];
                $totals[$k] += $r[$k];
            }
        }
        unset($r);

        return ['rows' => $rows] + $totals;
    }

    /** Every expense and every received purchase order: the Cash Disbursements Book. */
    protected function cashDisbursements(int $businessId, string $from, string $to): array
    {
        $db = db_connect();
        $rows = [];

        $expenses = $db->table('expenses')
            ->where('business_id', $businessId)
            ->where('expense_date >=', $from)
            ->where('expense_date <=', $to)
            ->get()->getResultArray();
        foreach ($expenses as $e) {
            $rows[] = [
                'date'        => $e['expense_date'],
                'reference'   => 'EXP-' . str_pad((string) $e['id'], 5, '0', STR_PAD_LEFT),
                'particulars' => $e['description'] ?: $e['category'],
                'account'     => $e['category'] . ' Expense',
                'amount'      => (float) $e['amount'],
            ];
        }

        $purchases = $db->table('purchase_orders po')
            ->select('po.po_number, po.total_amount, po.received_at, s.name AS supplier')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->where('po.business_id', $businessId)
            ->where('po.status', 'received')
            ->where('DATE(po.received_at) >=', $from)
            ->where('DATE(po.received_at) <=', $to)
            ->get()->getResultArray();
        foreach ($purchases as $p) {
            $rows[] = [
                'date'        => substr($p['received_at'], 0, 10),
                'reference'   => $p['po_number'],
                'particulars' => 'Purchase from ' . ($p['supplier'] ?: 'supplier'),
                'account'     => 'Purchases',
                'amount'      => (float) $p['total_amount'],
            ];
        }

        usort($rows, fn ($a, $b) => [$a['date'], $a['reference']] <=> [$b['date'], $b['reference']]);

        return ['rows' => $rows, 'total' => array_sum(array_column($rows, 'amount'))];
    }

    /** Account totals for the period, cash basis. */
    protected function generalLedger(array $receipts, array $disbursements): array
    {
        $accounts = [];
        $add = function (string $account, float $debit, float $credit) use (&$accounts) {
            $accounts[$account] ??= ['account' => $account, 'debit' => 0.0, 'credit' => 0.0];
            $accounts[$account]['debit'] += $debit;
            $accounts[$account]['credit'] += $credit;
        };

        // Sales: cash received (debit Cash), gross sales (credit Sales), discounts (debit Sales Discounts).
        $add('Cash', $receipts['net'], 0);
        $add('Sales Discounts', $receipts['discount'], 0);
        $add('Sales', 0, $receipts['gross']);

        // Disbursements: debit each account, credit Cash.
        foreach ($disbursements['rows'] as $d) {
            $add($d['account'], $d['amount'], 0);
        }
        $add('Cash', 0, $disbursements['total']);

        $order = ['Cash' => 0, 'Sales' => 1, 'Sales Discounts' => 2, 'Purchases' => 3];
        uasort($accounts, fn ($a, $b) => [$order[$a['account']] ?? 9, $a['account']] <=> [$order[$b['account']] ?? 9, $b['account']]);

        return [
            'accounts'    => array_values($accounts),
            'totalDebit'  => array_sum(array_column($accounts, 'debit')),
            'totalCredit' => array_sum(array_column($accounts, 'credit')),
            'cashBalance' => $receipts['net'] - $disbursements['total'],
        ];
    }

    protected function quarterReport(int $businessId, int $year, int $quarter): array
    {
        $months = [];
        $totals = ['transactions' => 0, 'gross' => 0.0, 'discount' => 0.0, 'net' => 0.0, 'purchases' => 0.0, 'expenses' => 0.0, 'income' => 0.0];

        for ($i = 0; $i < 3; $i++) {
            $month = ($quarter - 1) * 3 + 1 + $i;
            $from = sprintf('%04d-%02d-01', $year, $month);
            $to = date('Y-m-t', strtotime($from));

            $r = $this->cashReceipts($businessId, $from, $to);
            $d = $this->cashDisbursements($businessId, $from, $to);
            $purchases = array_sum(array_map(fn ($x) => $x['account'] === 'Purchases' ? $x['amount'] : 0, $d['rows']));
            $expenses = $d['total'] - $purchases;

            $row = [
                'label'        => date('F Y', strtotime($from)),
                'transactions' => $r['transactions'],
                'gross'        => $r['gross'],
                'discount'     => $r['discount'],
                'net'          => $r['net'],
                'purchases'    => $purchases,
                'expenses'     => $expenses,
                'income'       => $r['net'] - $purchases - $expenses,
            ];
            $months[] = $row;
            foreach ($totals as $k => $_) {
                $totals[$k] += $row[$k];
            }
        }

        return [
            'months'        => $months,
            'totals'        => $totals,
            'percentageTax' => round($totals['net'] * self::PERCENTAGE_TAX_RATE, 2),
            'periodLabel'   => date('F', strtotime(sprintf('%04d-%02d-01', $year, ($quarter - 1) * 3 + 1)))
                . ' – ' . date('F Y', strtotime(sprintf('%04d-%02d-01', $year, $quarter * 3))),
        ];
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * First and last dates that have any book entry (completed sale, expense,
     * or received purchase order) for this business, or null if there are none.
     */
    protected function dataRange(int $businessId): ?array
    {
        $row = db_connect()->query(
            "SELECT MIN(d) AS first_date, MAX(d) AS last_date FROM (
                SELECT DATE(created_at) AS d FROM sales WHERE business_id = ? AND status = 'completed'
                UNION ALL SELECT expense_date FROM expenses WHERE business_id = ?
                UNION ALL SELECT DATE(received_at) FROM purchase_orders WHERE business_id = ? AND status = 'received'
            ) t",
            [$businessId, $businessId, $businessId]
        )->getRowArray();

        return !empty($row['first_date']) ? [$row['first_date'], $row['last_date']] : null;
    }

    /**
     * Date range from presets (all, month, quarter, year) or custom dates.
     * With no choice made, shows every record on file ("all").
     */
    protected function resolveRange(): array
    {
        $period = $this->request->getGet('period');
        $from = $this->request->getGet('date_from');
        $to = $this->request->getGet('date_to');
        $valid = fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);

        $noChoice = !$period && !($valid($from) && $valid($to));
        if ($period === 'all' || $noChoice) {
            $range = $this->dataRange($this->currentBusinessId());
            if ($range) {
                return [$range[0], $range[1], 'all'];
            }
            $period = 'month'; // no records yet: fall back to this month
        }

        if ($period === 'quarter') {
            $q = intdiv((int) date('n') - 1, 3);
            $from = sprintf('%04d-%02d-01', (int) date('Y'), $q * 3 + 1);
            $to = date('Y-m-d');
        } elseif ($period === 'year') {
            $from = date('Y-01-01');
            $to = date('Y-m-d');
        } elseif ($valid($from) && $valid($to)) {
            $period = 'custom';
            if ($from > $to) {
                [$from, $to] = [$to, $from];
            }
        } else {
            $period = 'month';
            $from = date('Y-m-01');
            $to = date('Y-m-d');
        }

        return [$from, $to, $period];
    }

    /** Year/quarter from the request; by default the quarter of the latest record on file. */
    protected function resolveQuarter(): array
    {
        $range = $this->dataRange($this->currentBusinessId());
        $latest = $range ? strtotime($range[1]) : time();
        $year = (int) ($this->request->getGet('year') ?: date('Y', $latest));
        $quarter = (int) ($this->request->getGet('quarter') ?: intdiv((int) date('n', $latest) - 1, 3) + 1);
        $year = max(2000, min(2100, $year));
        $quarter = max(1, min(4, $quarter));
        return [$year, $quarter];
    }

    protected function money(float $v): string
    {
        return number_format($v, 2, '.', '');
    }
}
