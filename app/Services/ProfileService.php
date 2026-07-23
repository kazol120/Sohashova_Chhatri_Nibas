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
    public function updateProfile(Request $request, $user)
    {
        $in = $request->except(['_token', 'cover_image']);

        if ($request->hasFile('user_image')) {
            if ($user->user_image && $user->user_image !== 'user.png') {
                if (file_exists(public_path('storage/user/' . $user->user_image))) {
                    @unlink(public_path('storage/user/' . $user->user_image));
                }
            }

            $image = $request->file('user_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            $destinationPath = public_path('storage/user');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $image->move($destinationPath, $imageName);

            $in['user_image'] = $imageName;
            $in['image']      = $imageName;
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
