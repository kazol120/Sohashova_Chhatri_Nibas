<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Backend\RoomBookingHistory;

class ProfileController extends Controller
{
    protected $profileService;
    public function __construct(ProfileService $profileService)
    {
        $this->middleware('auth');
        $this->profileService = $profileService;
    }

    public function profile()
    {
        $id = Auth::id();
        $user = $this->profileService->getUser($id);
        $data['page_title'] = "My Profile Information";
        $data['user'] = $user;

        $booking = null;
        if (!$user->hasRole('admin') && !$user->hasRole('staffs')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $booking = RoomBookingHistory::where('status', 0)
                ->where(function ($q) use ($user, $cleanPhone) {
                    if (!empty($user->email)) {
                        $q->orWhere('email', $user->email);
                    }
                    if (!empty($cleanPhone)) {
                        $q->orWhere('phone', 'like', "%{$cleanPhone}%");
                    }
                })->latest()->first();
        }
        $data['booking'] = $booking;

        return view('backend.profile.profile', $data);
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|max:17',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'user_image' => 'nullable|file|mimes:jpg,png,jpeg,pdf,webp|max:4096',
            'cover_image' => 'nullable|file|mimes:jpg,png,jpeg,pdf,webp|max:4096',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:30',
            'father_nid' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:30',
            'mother_nid' => 'nullable|string|max:100',
            'user_type' => 'nullable|string|max:100',
            'institution_name' => 'nullable|string|max:255',
            'workplace_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $this->profileService->updateProfile($request, $user);
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function profileChangeStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:users,id,'.$request->user_id,
        ]);
        try{
            $this->profileService->changeStatus($request);
            return redirect()->back()->with('success', 'Profile updated successfully');
        }catch(\Exception $exception){
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }

    public function passwordChange()
    {
        $id = Auth::id();
        $data['page_title'] = "Change Password";
        $data['user'] = $this->profileService->getUser($id);
        session()->put('attempt', 3);
        return view('backend.profile.change-password',$data);
    }

    public function oldPasswordCheck(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string|min:6|max:6',
        ]);

        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }
        return redirect()->route('new-password')->with('success', 'Old password is correct.');
    }

    public function newPassword(Request $request)
    {
        $id = Auth::id();
        $data['page_title'] = "Set you new password";
        $data['user'] = $this->profileService->getUser($id);
        return view('backend.profile.new-password',$data);
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|max:6',
            'confirm_password' => 'required|string|min:6|max:6|same:new_password',
        ]);
        $id = Auth::id();
        $this->profileService->changePassword($request,$id);
        return redirect()->route('profile')->with('success', 'Your password has been updated successfully.');
    }
}
