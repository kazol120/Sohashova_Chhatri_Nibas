<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/backend/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        return redirect()->to(url('/?login=1'));
    }

    public function username()
    {
        return 'login';
    }

    protected function credentials(Request $request)
    {
        $login = trim($request->input('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'login' => [trans('auth.failed')],
        ]);
    }


protected function sendLoginResponse(Request $request)
{
    $request->session()->regenerate();

    $this->clearLoginAttempts($request);

    $user = Auth::user();
    if ((int) $user->status === 0) {

        Auth::logout();

        return redirect()->to(url('/?login=1'))->with(
            'warning',
            'Your account is temporarily deactivated. Please contact your admin.'
        );
    }

    // status 1 and 2 allow
    $intended = session('url.intended');
    if ($intended && (str_contains($intended, '/api/') || str_contains($intended, 'pending-complaints'))) {
        session()->forget('url.intended');
    }

    return redirect()->intended('/backend/dashboard');
}


protected function attemptLogin(Request $request)
{
    $login = trim($request->input('login'));
    $password = $request->input('password');

    $cleanPhone = preg_replace('/[^0-9]/', '', $login);
    $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

    $users = \App\Models\User::where(function ($q) use ($login, $cleanPhone, $last10) {
        $q->where('email', $login)
          ->orWhere('name', $login)
          ->orWhere('phone', $login);
        if (!empty($cleanPhone) && strlen($cleanPhone) >= 6) {
            $q->orWhere('phone', 'like', "%{$last10}");
            $q->orWhere('phone', '+88' . $cleanPhone);
            $q->orWhere('phone', '88' . $cleanPhone);
        }
    })->where(function ($q) {
        $q->whereIn('status', [1, 2])->orWhereNull('status');
    })->get();

    foreach ($users as $user) {
        if (Hash::check($password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            return true;
        }
    }

    return false;
}



}