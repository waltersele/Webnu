<?php

namespace App\Http\Middleware;

use Closure;

class ForceUtf8Charset
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type', '');

        if ($contentType === '' || stripos($contentType, 'text/html') !== false) {
            if (stripos($contentType, 'charset') === false) {
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            }
        }

        return $response;
    }
}
