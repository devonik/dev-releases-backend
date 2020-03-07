<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
class HttpsProtocol
{
    public function handle(Request $request, Closure $next)
    {
        error_log("test");
        if (!$request->secure() && App::environment() === 'production') {
            error_log("test");
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
