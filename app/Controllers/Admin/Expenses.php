<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExpenseModel;

class Expenses extends BaseController
{
    protected $expenseModel;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
    }

    protected function guard()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }
        return null;
    }

    /**
     * List + report. Defaults to the current calendar month so the page
     * opens on something relevant, with quick presets and a custom range.
     */
    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businessId = $this->currentBusinessId();
        $period = $this->request->getGet('period');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        if (in_array($period, ['week', 'month', 'year'], true)) {
            $now = new \DateTime();
            switch ($period) {
                case 'week':
                    $start = (clone $now)->modify('monday this week');
                    break;
                case 'year':
                    $start = (clone $now)->setDate((int) $now->format('Y'), 1, 1);
                    break;
                case 'month':
                default:
                    $start = (clone $now)->modify('first day of this month');
                    break;
            }
            $dateFrom = $start->format('Y-m-d');
            $dateTo = $now->format('Y-m-d');
        } elseif (!$dateFrom && !$dateTo) {
            // Default view: current calendar month.
            $dateFrom = date('Y-m-01');
            $dateTo = date('Y-m-d');
            $period = 'month';
        } else {
            $period = 'custom';
        }

        $categoryFilter = $this->request->getGet('category');

        $query = $this->expenseModel->forBusiness($businessId)
            ->where('expense_date >=', $dateFrom)
            ->where('expense_date <=', $dateTo);

        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        $expenses = $query->orderBy('expense_date', 'DESC')->orderBy('id', 'DESC')->findAll();

        $data['expenses'] = $expenses;
        $data['totalAmount'] = array_sum(array_column($expenses, 'amount'));
        $data['byCategory'] = $this->expenseModel->getTotalsByCategory($businessId, $dateFrom, $dateTo);
        $data['categories'] = ExpenseModel::CATEGORIES;
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        $data['period'] = $period;
        $data['categoryFilter'] = $categoryFilter;

        return view('admin/expenses/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if ($this->request->getMethod() === 'POST') {
            $category = $this->request->getPost('category');
            if ($category === 'Other') {
                $custom = trim((string) $this->request->getPost('custom_category'));
                if ($custom !== '') {
                    $category = $custom;
                }
            }

            $data = [
                'business_id'  => $this->currentBusinessId(),
                'category'     => $category,
                'description'  => $this->request->getPost('description'),
                'amount'       => (float) $this->request->getPost('amount'),
                'expense_date' => $this->request->getPost('expense_date') ?: date('Y-m-d'),
                'created_by'   => session()->get('user_id'),
                'created_at'   => date('Y-m-d H:i:s'),
            ];

            $this->expenseModel->insert($data);
            $this->logAudit('expense.created', 'Logged ' . $data['category'] . ' expense of ₱' . number_format($data['amount'], 2));
            session()->setFlashdata('success', 'Expense recorded');
            return redirect()->to('/admin/expenses');
        }

        $data['categories'] = ExpenseModel::CATEGORIES;
        return view('admin/expenses/create', $data);
    }

    public function edit($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $expense = $this->expenseModel->forBusiness($this->currentBusinessId())->find($id);
        if (!$expense) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Expense not found');
        }

        if ($this->request->getMethod() === 'POST') {
            $category = $this->request->getPost('category');
            if ($category === 'Other') {
                $custom = trim((string) $this->request->getPost('custom_category'));
                if ($custom !== '') {
                    $category = $custom;
                }
            }

            $updateData = [
                'category'     => $category,
                'description'  => $this->request->getPost('description'),
                'amount'       => (float) $this->request->getPost('amount'),
                'expense_date' => $this->request->getPost('expense_date'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            $this->expenseModel->update($id, $updateData);
            $this->logAudit('expense.updated', 'Updated ' . $updateData['category'] . ' expense');
            session()->setFlashdata('success', 'Expense updated');
            return redirect()->to('/admin/expenses');
        }

        $data['expense'] = $expense;
        $data['categories'] = ExpenseModel::CATEGORIES;
        return view('admin/expenses/edit', $data);
    }

    public function delete($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $expense = $this->expenseModel->forBusiness($this->currentBusinessId())->find($id);
        if ($expense) {
            $this->expenseModel->delete($id);
            $this->logAudit('expense.deleted', 'Deleted ' . $expense['category'] . ' expense of ₱' . number_format($expense['amount'], 2));
            session()->setFlashdata('success', 'Expense deleted');
        }

        return redirect()->to('/admin/expenses');
    }
}
