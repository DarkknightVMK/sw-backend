<?php

namespace App\Http\Middleware;
use Closure;

class slashesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $flag)
    {
        if ($flag=="remove") {
            if (str_ends_with($request->getPathInfo(), '/')) {
                $newval = rtrim($request->getPathInfo(), "/");
                header("HTTP/1.1 301 Moved Permanently");
                header("Location:$newval");
                exit();
            }
        } else {
            if (!str_ends_with($request->getPathInfo(), '/')) {
                $newval =$request->getPathInfo().'/';
                header("HTTP/1.1 301 Moved Permanently");
                header("Location:$newval");
                exit();
            }
        }
        return $next($request);
    }
}