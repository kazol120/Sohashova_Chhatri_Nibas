<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Import the correct Request class

class ProfileService {


    public function getUser($id)
    {
        return User::with('roles')->find($id);
    }
    public function updateProfile(Request $request,$user)
    {
        $in = $request->except('_token');

        if($request->hasFile('user_image'))
        {
            // Check if the user already has an existing file
            if ($user->user_image) {
                // Delete the existing file if it exists
                if ($user->user_image !== 'user.png' && Storage::exists($user->user_image)) {
                    Storage::delete($user->user_image);
                }
            }

            $image = $request->file('user_image');
            $imageName = uniqid().'.'.$image->getClientOriginalExtension();
            // Store the file in the 'public/user_images' directory
            $image->storeAs('public/user', $imageName);
            $in['user_image'] = $imageName;

        }
        if($request->hasFile('cover_image'))
        {
            // Check if the user already has an existing file
            if ($user->cover_image) {
                // Delete the existing file if it exists
                if ($user->cover_image !== 'cover.png' && Storage::exists($user->cover_image)) {
                    Storage::delete($user->cover_image);
                }
            }

            $image = $request->file('cover_image');
            $imageName = 'cover_'.uniqid().'.'.$image->getClientOriginalExtension();
            // Store the file in the 'public/user_images' directory
            $image->storeAs('public/user', $imageName);
            $in['cover_image'] = $imageName;

        }

        $user->update($in);
        return $user;
    }
    public function changeStatus(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        if($user->status == 1) {
            $user->status = 0;
            $user->save();
        }else{
            $user->status = 1;
            $user->save();
        }
        return $user;
    }

    public function changePassword(Request $request,$id)
    {
        $user = $this->getUser($id);
        $user->temp_password = $request->new_password;
        $user->password = bcrypt($request->new_password);
        $user->save();
        return $user;
    }
}
