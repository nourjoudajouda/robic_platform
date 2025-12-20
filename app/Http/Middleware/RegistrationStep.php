<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RegistrationStep
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
        $user = auth()->user();
        
        // Skip check if profile is complete or if accessing authorization routes
        if ($user->profile_complete || $request->is('user/authorization*') || $request->is('user/resend-verify*') || $request->is('user/verify-*')) {
            return $next($request);
        }
        
        if (!$user->profile_complete) {
            if ($request->is('api/*')) {
                $notify[] = 'Please complete your profile to go next';
                return response()->json([
                    'remark'=>'profile_incomplete',
                    'status'=>'error',
                    'message'=>['error'=>$notify],
                ]);
            }else{
                // Redirect to dashboard instead of user-data page
                return to_route('user.home');
            }
        }
        return $next($request);
    }
}
