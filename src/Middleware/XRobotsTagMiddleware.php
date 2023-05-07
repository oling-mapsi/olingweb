// XRobotsTagMiddleware.php

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XRobotsTagMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $next($request, $response);

        $response->headers->set('X-Robots-Tag', 'index');

        return $response;
    }
}