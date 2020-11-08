<?php

namespace App\Http\Middleware;

use Closure;

class decryptInputParams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($request->is_api_test)) {
            return $next($request);
        }

        $input = $request->all();

        if (isset($input['data'])) {

            $data = decryptData(getPassphrase(), $input['data']);
            unset($input['data']);
            $request->replace($data);
        }
        return $next($request);
    }
}
