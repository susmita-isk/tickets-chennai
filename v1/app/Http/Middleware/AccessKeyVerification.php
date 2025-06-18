<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AccessKeyVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $this->accessKey = "450!#kc@nHKRKkbngPiLnsg@498";
        if($request->accessKey == $this->accessKey)
        {
            return $next($request);
        }else{
            $this->apiResponse['successCode'] = -1;
            $this->apiResponse['message'] = "Invalid Access Key";
            return response()->json($this->apiResponse);
        }

    }
}
