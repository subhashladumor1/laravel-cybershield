<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use CyberShield\ApiSecurity\ApiSecurityManager;

class ApiSecurityGatewayMiddleware
{
    protected $manager;

    public function __construct(ApiSecurityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Execute the 4-layer API security verification
        $this->manager->verify($request);

        $response = $next($request);

        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        $this->manager->terminate($request);
    }
}
