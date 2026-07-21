<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    protected $passwordResetService;
    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function sendOtp(Request $request)
    {

        $valid = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput();
        }
        $this->passwordResetService->makeOtp($request->email);
        return view('auth.passwords.otp')->with('success','OTP Send!');
    }
    public function checkOtp(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'otp' => 'required|exists:otps,otp',
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput();
        }
        $token = $this->passwordResetService->verifyOtp($request);
        if ($token['status'] == 'true'){
            return redirect()->route('verify-token',$token['token'])->with('success','OTP verified successfully');
        }else{
            return back()->with('warning','OTP did not match!');

        }

    }
    public function validateToken($token)
    {
        $sessionToken = session()->get('url_token');      // Get token from session
        $sessionExpire = session()->get('expires_at');    // Get expiration time

        if (!$sessionToken || !$sessionExpire) {
           return back()->with('warning','Token not set properly');
        }

        // Check if the session token has expired
        if (now()->greaterThan($sessionExpire)) {
            session()->forget(['user_email','url_token', 'expires_at']); // Clear expired session data
            return back()->with('warning','Token Expired!');
        }

        // Check if the token matches
        if ($sessionToken == $token) {
            return view('auth.passwords.new-password');
        } else {
            return back()->with('error','Token not matched!');
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
        ]);
        $this->passwordResetService->changePassword($request);
        // Redirect with a success message
        return redirect()->route('login')->with('success', 'Your password has been updated successfully.');
    }

}
