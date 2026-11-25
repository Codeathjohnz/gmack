<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessModel;
use App\Models\ItemModel;
use App\Models\UserModel;

/**
 * Platform-level tenant management. Only the superadmin account can create,
 * edit or switch into any business; every other role is bounced home.
 */
class Businesses extends BaseController
{
    protected $businessModel;
    protected $userModel;

    public function __construct()
    {
        $this->businessModel = new BusinessModel();
        $this->userModel = new UserModel();
    }

    protected function guard()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'superadmin') {
            return redirect()->to('/auth');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $businesses = $this->businessModel->orderBy('created_at', 'DESC')->findAll();
        $itemModel = new ItemModel();

        foreach ($businesses as &$biz) {
            $biz['user_count'] = $this->userModel->where('business_id', $biz['id'])->countAllResults();
            $biz['item_count'] = $itemModel->where('business_id', $biz['id'])->countAllResults();
            $biz['sale_count'] = db_connect()->table('sales')->where('business_id', $biz['id'])->countAllResults();
            $biz['owner'] = $biz['owner_user_id'] ? $this->userModel->find($biz['owner_user_id']) : null;
        }

        return view('admin/businesses/index', ['businesses' => $businesses]);
    }

    public function create()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $name = trim((string) $this->request->getPost('name'));
            $username = trim((string) $this->request->getPost('username'));
            $email = trim((string) $this->request->getPost('email'));
            $password = (string) $this->request->getPost('password');

            if (strlen($name) < 2 || strlen($username) < 3 || strlen($password) < 6 || $email === '') {
                session()->setFlashdata('error', 'Please fill in all fields (password min. 6 characters).');
                return redirect()->back()->withInput();
            }

            if ($this->userModel->where('username', $username)->first() || $this->userModel->where('email', $email)->first()) {
                session()->setFlashdata('error', 'That username or email is already taken.');
                return redirect()->back()->withInput();
            }

            $slug = $this->businessModel->generateUniqueSlug($name);
            $businessId = $this->businessModel->insert([
                'name'     => $name,
                'slug'     => $slug,
                'industry' => 'Hardware & Construction Supply',
                'status'   => 'active',
            ], true);

            $userId = $this->userModel->insert([
                'business_id' => $businessId,
                'username'    => $username,
                'email'       => $email,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'role'        => 'admin',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
            ], true);

            $this->businessModel->update($businessId, ['owner_user_id' => $userId]);
            $this->logAudit('business.created', "Created business \"{$name}\" (owner: {$username})");

            session()->setFlashdata('success', "Business \"{$name}\" created.");
            return redirect()->to('/admin/businesses');
        }

        return view('admin/businesses/create');
    }

    public function edit($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $business = $this->businessModel->find($id);
        if (!$business) {
            session()->setFlashdata('error', 'Business not found.');
            return redirect()->to('/admin/businesses');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $this->businessModel->update($id, [
                'name'    => trim((string) $this->request->getPost('name')),
                'address' => trim((string) $this->request->getPost('address')),
                'phone'   => trim((string) $this->request->getPost('phone')),
                'status'  => $this->request->getPost('status') === 'inactive' ? 'inactive' : 'active',
            ]);
            $this->logAudit('business.updated', 'Updated business "' . $business['name'] . '"');
            session()->setFlashdata('success', 'Business updated.');
            return redirect()->to('/admin/businesses');
        }

        return view('admin/businesses/edit', ['business' => $business]);
    }

    public function toggle($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $business = $this->businessModel->find($id);
        if ($business) {
            $newStatus = $business['status'] === 'active' ? 'inactive' : 'active';
            $this->businessModel->update($id, ['status' => $newStatus]);
            $this->logAudit('business.' . $newStatus, ucfirst($newStatus) . ' business "' . $business['name'] . '"');
        }

        return redirect()->to('/admin/businesses');
    }

    /**
     * Permanently delete a business and everything it owns. Every tenant table
     * has an ON DELETE CASCADE foreign key to businesses, so removing the
     * business row removes its users, items, sales, expenses, POs, etc.
     * Audit log rows are kept (their business_id is SET NULL) as history.
     * The superadmin must type the business name exactly to confirm.
     */
    public function delete($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/businesses');
        }

        $business = $this->businessModel->find($id);
        if (!$business) {
            session()->setFlashdata('error', 'Business not found.');
            return redirect()->to('/admin/businesses');
        }

        $typed = trim((string) $this->request->getPost('confirm_name'));
        if ($typed !== $business['name']) {
            session()->setFlashdata('error', 'Business not deleted: the name you typed did not match "' . esc($business['name']) . '".');
            return redirect()->to('/admin/businesses');
        }

        $db = db_connect();

        // Collect uploaded files before the rows that point to them are gone.
        $files = array_merge(
            array_column($db->table('items')->select('image')->where('business_id', $id)->where('image IS NOT NULL')->get()->getResultArray(), 'image'),
            array_column($db->table('payment_methods')->select('qr_code')->where('business_id', $id)->where('qr_code IS NOT NULL')->get()->getResultArray(), 'qr_code')
        );
        $counts = [
            'users' => $db->table('users')->where('business_id', $id)->countAllResults(),
            'items' => $db->table('items')->where('business_id', $id)->countAllResults(),
            'sales' => $db->table('sales')->where('business_id', $id)->countAllResults(),
        ];

        $db->transStart();
        $this->businessModel->delete($id);
        $db->transComplete();

        if (!$db->transStatus()) {
            session()->setFlashdata('error', 'Could not delete "' . esc($business['name']) . '". Nothing was removed.');
            return redirect()->to('/admin/businesses');
        }

        // Remove the business's uploaded files (only paths inside public/uploads).
        $uploadsRoot = realpath(FCPATH . 'uploads');
        foreach ($files as $rel) {
            $path = realpath(FCPATH . $rel);
            if ($path && $uploadsRoot && str_starts_with($path, $uploadsRoot) && is_file($path)) {
                @unlink($path);
            }
        }
        $alarmDir = FCPATH . 'uploads/alarms/' . (int) $id;
        if (is_dir($alarmDir)) {
            foreach (glob($alarmDir . '/*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($alarmDir);
        }

        // If the superadmin was switched into this business, drop that context.
        if ((int) session()->get('business_id') === (int) $id) {
            session()->remove(['impersonating', 'original_role', 'business_id', 'business_name']);
        }

        $this->logAudit('business.deleted', sprintf(
            'Deleted business "%s" (#%d) with %d users, %d items and %d sales',
            $business['name'], $id, $counts['users'], $counts['items'], $counts['sales']
        ));

        session()->setFlashdata('success', 'Business "' . esc($business['name']) . '" and all of its data were deleted.');
        return redirect()->to('/admin/businesses');
    }

    /**
     * Support access: view a tenant's admin panel as if logged in there.
     * The superadmin's real identity is stashed so switchBack() can restore it.
     */
    public function switchTo($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $business = $this->businessModel->find($id);
        if (!$business) {
            session()->setFlashdata('error', 'Business not found.');
            return redirect()->to('/admin/businesses');
        }

        $this->logAudit('business.switch_to', 'Superadmin switched into business "' . $business['name'] . '" for support access');

        session()->set([
            'impersonating'   => true,
            'original_role'   => session()->get('role'),
            'role'            => 'admin',
            'business_id'     => $business['id'],
            'business_name'   => $business['name'],
        ]);

        return redirect()->to('/admin/dashboard');
    }

    public function switchBack()
    {
        if (!session()->get('impersonating')) {
            return redirect()->to('/admin/businesses');
        }

        session()->set([
            'role' => session()->get('original_role'),
        ]);
        session()->remove(['impersonating', 'original_role', 'business_id', 'business_name']);

        return redirect()->to('/admin/businesses');
    }
}
