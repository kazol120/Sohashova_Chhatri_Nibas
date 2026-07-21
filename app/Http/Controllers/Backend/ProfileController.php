<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $data['page_title'] = "My Profile Information";
        $data['user'] = $this->profileService->getUser($id);
        return view('backend.profile.profile',$data);
    }
    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|max:17',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'user_image' => 'file|mimes:jpg,png,jpeg,pdf|max:2048',
            'cover_image' => 'file|mimes:jpg,png,jpeg,pdf|max:2048',
        ]);

        $this->profileService->updateProfile($request,$user);
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
        // Initialize the session attempts to 3
        session()->put('attempt', 3);
        return view('backend.profile.change-password',$data);
    }

    public function oldPasswordCheck(Request $request)
    {
        // Validate the input for the old password
        $request->validate([
            'old_password' => 'required|string|min:6',
        ]);

        // Check if the entered old password matches the current password
        if (!Hash::check($request->old_password, auth()->user()->password)) {
            // Add an error message to the session
            return back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }
        // Continue with the process (e.g., showing the next step)
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
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:new_password',
        ]);
        $id = Auth::id();
        $this->profileService->changePassword($request,$id);
        // Redirect with a success message
        return redirect()->route('profile')->with('success', 'Your password has been updated successfully.');

    }


}
