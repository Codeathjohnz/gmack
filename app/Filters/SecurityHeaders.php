<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow the application to be framed
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Remove restrictive CSP frame-ancestors if it exists
        $response->removeHeader('Content-Security-Policy');
        
        // Set permissive CSP that allows framing
        $response->setHeader('Content-Security-Policy', "frame-ancestors 'self' http: https:");
        
        // Allow clickjacking protection but permissive
        $response->setHeader('X-Frame-Options', 'ALLOWALL');
        
        return $response;
    }
}
