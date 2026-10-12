<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserStatusCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && $user->status !== null && (int) $user->status === 0) {
                // Check if user has an active booking in RoomBookingHistory
                $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
                $last10 = substr($cleanPhone, -10);
                $hasActiveBooking = \App\Models\Backend\RoomBookingHistory::where('status', 1)
                    ->whereNull('today_check_out')
                    ->where(function ($q) use ($user, $last10) {
                        if (!empty($user->email)) $q->orWhere('email', $user->email);
                        if (!empty($last10)) $q->orWhere('phone', 'like', "%{$last10}%");
                    })
                    ->exists();

                if ($hasActiveBooking) {
                    $user->update(['status' => 1]);
                } else {
                    Auth::logout();

                    return redirect()->to(url('/?login=1'))->with(
                        'warning',
                        'Your account is temporarily deactivated. Please contact your admin.'
                    );
                }
            }
        }

        return $next($request);
    }
}
