<?php
namespace App\Services;

use App\Models\Backend\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PasswordResetService{

    public function makeOtp($email)
    {
        //delete all previous
        Otp::where('email', $email)->delete();
        //get user from the email
        $user = User::where('email', $email)->first();

        //make new otp data
        $in['email'] = $email;
        $in['user_id'] = $user->id;
        $in['otp'] = $this->generateOtp();
        $in['expires_at'] = Carbon::now(+6)->addMinutes(10);
        $otp = Otp::create($in);

        return $otp;
    }

    public function verifyOtp(Request $request)
    {
       $otp = Otp::where('otp',$request->otp)->latest()->first();
       if($otp){
           return [
               'status' => true,
               'token' => $this->makeSession($otp->email),
           ];
       }else{
           return [
               'status' => false,
               'token' => "",
           ];
       }
    }

    public function changePassword(Request $request)
    {
        $email = session('user_email');

        $user = User::where('email', $email)->first();
        $user->temp_password = $request->password;
        $user->password = bcrypt($request->password);
        $user->save();

        Otp::where('email', $email)->delete();
        session()->forget(['user_email', 'url_token', 'expires_at']);
        return $user;
    }
    private function generateOtp()
    {
        return random_int(100000, 999999);
    }
    private function makeSession($email)
    {
        //generate the token for redirect url
        $token = Str::random(64);
        session()->put('user_email', $email);
        session()->put('url_token', $token);
        session()->put('expires_at', now()->addMinutes(10));//expires the session
        return $token;
    }
}
