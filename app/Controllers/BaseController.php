<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * The tenant (business) the logged-in user belongs to. NULL for a
     * platform-level superadmin who hasn't switched into a business yet.
     */
    protected function currentBusinessId(): ?int
    {
        $id = session()->get('business_id');
        return $id !== null ? (int) $id : null;
    }

    protected function currentBusiness(): ?array
    {
        $id = $this->currentBusinessId();
        if (!$id) {
            return null;
        }

        return (new \App\Models\BusinessModel())->find($id);
    }

    protected function isSuperAdmin(): bool
    {
        return session()->get('role') === 'superadmin';
    }

    /**
     * Record one accountability entry. business_id/user_id/ip are filled in
     * automatically from the current session/request.
     *
     * @param string $action      short machine code, e.g. 'item.updated'
     * @param string $description human-readable detail, e.g. 'Updated item "Cement 40kg"'
     */
    protected function logAudit(string $action, string $description): void
    {
        (new \App\Models\AuditLogModel())->insert([
            'business_id' => $this->currentBusinessId(),
            'user_id'     => session()->get('user_id'),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $this->request->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
