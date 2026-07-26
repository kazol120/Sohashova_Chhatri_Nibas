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
    $oldPhone = $user->getOriginal('phone') ?? $user->phone;
    $cleanPhone = preg_replace('/[^0-9]/', '', $oldPhone);
    $last11Digits = substr($cleanPhone, -11);
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
    }
    $user->update($in);
    $user = $user->fresh();
    $staff = \App\Models\Backend\Staffs::where('phone', 'LIKE', '%' . $last11Digits)->first();
    if ($staff) {
        $staffData = [
            'name'              => $request->name,
            'phone'             => $request->phone,
            'email'             => $request->email,
            'permanent_address' => $request->address,
        ];
        if ($request->hasFile('user_image')) {
            $staffFolder = public_path('staff_images');
            if (!file_exists($staffFolder)) {
                mkdir($staffFolder, 0777, true);
            }
            if ($staff->photo && file_exists($staffFolder . '/' . $staff->photo)) {
                @unlink($staffFolder . '/' . $staff->photo);
            }
            copy(public_path('storage/user/' . $in['user_image']), $staffFolder . '/' . $in['user_image']);
            $staffData['photo'] = $in['user_image']; 
        }
        $staff->update($staffData);
    }
    $bookingHistory = \App\Models\Backend\RoomBookingHistory::where('phone', 'LIKE', '%' . $last11Digits)->first();
    if ($bookingHistory) {
        $bookingData = [
            'full_name' => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('room_booking_histories', 'address')) {
            $bookingData['address'] = $request->address;
        }
        if ($request->hasFile('user_image')) {
            $bookingFolder = public_path('bookingsimage');
            if (!file_exists($bookingFolder)) {
                mkdir($bookingFolder, 0777, true);
            }
            if ($bookingHistory->image && file_exists($bookingFolder . '/' . $bookingHistory->image)) {
                @unlink($bookingFolder . '/' . $bookingHistory->image);
            }
            copy(public_path('storage/user/' . $in['user_image']), $bookingFolder . '/' . $in['user_image']);
            $bookingData['image'] = $in['user_image']; 
        }
        $bookingHistory->update($bookingData);
    }
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
