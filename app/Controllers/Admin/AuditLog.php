<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\BusinessModel;
use App\Models\UserModel;

class AuditLog extends BaseController
{
    protected $auditLogModel;
    protected $userModel;

    public function __construct()
    {
        $this->auditLogModel = new AuditLogModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || !in_array(session()->get('role'), ['admin', 'superadmin'], true)) {
            return redirect()->to('/auth');
        }

        $query = $this->auditLogModel->orderBy('created_at', 'DESC');

        // Admins only ever see their own business's history; superadmin sees
        // everything (including platform-level entries with a NULL business_id).
        if (!$this->isSuperAdmin()) {
            $query->where('business_id', $this->currentBusinessId());
        }

        $logs = $query->findAll(500);

        $businessModel = new BusinessModel();
        $businessNames = [];
        $userNames = [];

        foreach ($logs as &$log) {
            if ($log['business_id']) {
                if (!isset($businessNames[$log['business_id']])) {
                    $biz = $businessModel->find($log['business_id']);
                    $businessNames[$log['business_id']] = $biz['name'] ?? 'Unknown business';
                }
                $log['business_name'] = $businessNames[$log['business_id']];
            } else {
                $log['business_name'] = 'Platform';
            }

            if ($log['user_id']) {
                if (!isset($userNames[$log['user_id']])) {
                    $user = $this->userModel->find($log['user_id']);
                    $userNames[$log['user_id']] = $user['username'] ?? 'Deleted user';
                }
                $log['username'] = $userNames[$log['user_id']];
            } else {
                $log['username'] = 'System';
            }
        }

        return view('admin/audit_log/index', ['logs' => $logs, 'isSuperAdmin' => $this->isSuperAdmin()]);
    }
}
