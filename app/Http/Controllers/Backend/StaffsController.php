<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Division;
use App\Models\Backend\District;
use App\Models\Backend\Thana;
use App\Models\Backend\Staffs;
use App\Models\Backend\Room;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;



class StaffsController extends Controller
{

    public function index()
    {
        return view('backend.staffs.staffs');
    }


  public function getstaffsGet(Request $request)
{
    $user = Auth::user();

    $perPage = (int) $request->get('per_page', 10);
    $search  = trim($request->get('search', ''));

    $q = Staffs::query()
        ->with(['division', 'district', 'thana'])
        ->where('status', 0);

    // staff role  data show
    if ($user->hasRole('staffs')) {
        $q->where(function ($query) use ($user) {
            $query->where('email', $user->email)
                  ->orWhere('phone', $user->phone);
        });
    }

    // admin  all data show
    if ($search !== '') {
        $q->where(function ($qq) use ($search) {
            $qq->where('employee_id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('nid_passport', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%")
                ->orWhereHas('division', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                ->orWhereHas('district', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                ->orWhereHas('thana', fn($fq) => $fq->where('name', 'like', "%{$search}%"));
        });
    }

    $staffs = $q->orderBy('id', 'desc')->paginate($perPage);

    return response()->json($staffs);
}




    // ===== STORE =====

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'employee_id'       => 'required|string|max:255|unique:staffs,employee_id',
        'name'              => 'required|string|max:255',
        'phone'             =>  ['required','string','max:20',
    function ($attribute, $value, $fail) {
        $existsStatus2 = \App\Models\User::where('phone', $value)
                    ->where('status', 2)
                    ->exists();
                if ($existsStatus2) {
                    $fail('This phone already exists for user.');
                }
            },
        ],
        'email' => [
            'nullable',
            'email',
            'max:255',
            function ($attribute, $value, $fail) {
                $existsStatus2 = \App\Models\User::where('email', $value)
                    ->where('status', 2)
                    ->exists();
                if ($existsStatus2) {
                    $fail('This email already exists for  user.');
                }
            },
        ],
        'nid_passport'      => 'nullable|string|max:255',
        'gender'            => 'nullable|string|max:20',
        'date_of_birth'     => 'nullable|date',
        'division_id'       => 'nullable|integer',
        'district_id'       => 'nullable|integer',
        'password'          => ['required', 'string'],
        'thana_id'          => 'nullable|integer',
        'permanent_address' => 'nullable|string',
        'designation'       => 'nullable|string|max:255',
        'department'        => 'nullable|string|max:255',
        'salary'            => 'nullable|numeric',
        'joining_date'      => 'nullable|date',
        'shift_time'        => 'nullable|string|max:255',
        'photo'             => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:5120',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);
    }
    DB::beginTransaction();
    try {
        $staff = new Staffs();
        $this->fillFields($staff, $request);

        $staff->status   = 0;
        $staff->password = $request->password;

        if ($request->hasFile('photo')) {
            $staff->photo = $this->savePhoto($request->file('photo'));
        }
        $staff->save();
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->filled('email') ? $request->email : null,
            'phone'         => $request->phone,
            'password'      => bcrypt($request->password),
            'status'        => 2,
            'temp_password' => $request->password,
        ]);

        $role = Role::firstOrCreate([
            'name'       => 'staffs',
            'guard_name' => 'web'
        ]);
        $user->assignRole($role);
        DB::commit();
        return response()->json([
            'message' => 'Staff created successfully',
            'data'    => $staff->load(['division', 'district', 'thana']),
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


// ===== UPDATE =====
public function update(Request $request, $id)
{
    $staff = Staffs::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'employee_id'       => 'required|string|max:255|unique:staffs,employee_id,' . $staff->id,
        'name'              => 'required|string|max:255',
        'phone'             => 'required|string|max:20',
        'email'             => 'nullable|email|max:255|unique:staffs,email,' . $staff->id,
        'nid_passport'      => 'nullable|string|max:255',
        'gender'            => 'nullable|string|max:20',
        'date_of_birth'     => 'nullable|date',
        'division_id'       => 'nullable|integer',
        'district_id'       => 'nullable|integer',
        'thana_id'          => 'nullable|integer',
        'password'          => ['nullable', 'string'],
        'permanent_address' => 'nullable|string',
        'designation'       => 'nullable|string|max:255',
        'department'        => 'nullable|string|max:255',
        'salary'            => 'nullable|numeric',
        'joining_date'      => 'nullable|date',
        'shift_time'        => 'nullable|string|max:255',
        'photo'             => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg,jfif,heic,heif,avif,bmp|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);
    }

    DB::beginTransaction();

    try {
        $this->fillFields($staff, $request);

        if ($request->filled('password')) {
            $staff->password = $request->password; // plain text staffs এ
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($staff->photo);
            $staff->photo = $this->savePhoto($request->file('photo'));
        }

        $staff->save();

        // phone + status  user 
        $user = User::where('phone', $staff->phone)
                    ->where('status', 2)
                    ->first();

        if ($user) {
            $userData = [
                'name'  => $request->name,
                'email' => $request->filled('email') ? $request->email : null,
                'phone' => $request->phone,
            ];

            if ($request->filled('password')) {
                $userData['password']      = bcrypt($request->password);
                $userData['temp_password'] = $request->password;
            }

            $user->update($userData);
        }

        DB::commit();

        return response()->json([
            'message' => 'Staff updated successfully',
            'data'    => $staff->load(['division', 'district', 'thana']),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id)
    {

        $staff = Staffs::findOrFail($id);
        $this->deletePhoto($staff->photo);
        $staff->delete();
        return response()->json(['message' => 'Staff deleted successfully']);
    }


    public function getDivisions()
    {
        return response()->json(Division::orderBy('name')->get());
    }


    public function getDistrictsByDivision($divisionId)
    {
        return response()->json(
            District::where('division_id', $divisionId)->orderBy('name')->get()
        );
    }


    public function getThanasByDistrict($districtId)
    {
        return response()->json(
            Thana::where('district_id', $districtId)->orderBy('name')->get()
        );
    }


    private function fillFields(Staffs $staff, Request $request): void
    {
        $staff->employee_id       = $request->employee_id;
        $staff->name              = $request->name;
        $staff->phone             = $request->phone;
        $staff->email             = $request->email;
        $staff->nid_passport      = $request->nid_passport;
        $staff->gender            = $request->gender;
        $staff->date_of_birth     = $request->date_of_birth;
        $staff->division_id       = $request->division_id  ?: null;
        $staff->district_id       = $request->district_id  ?: null;
        $staff->thana_id          = $request->thana_id     ?: null;
        $staff->permanent_address = $request->permanent_address;
        $staff->designation       = $request->designation;
        $staff->department        = $request->department;
        $staff->salary            = $request->salary       ?: 0;
        $staff->joining_date      = $request->joining_date;
        $staff->shift_time        = $request->shift_time;
    }

    private function savePhoto($file): string
    {
        $dir = public_path('staff_images');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return $filename;
    }


    private function deletePhoto(?string $photo): void
    {
        if ($photo && File::exists(public_path('staff_images/' . $photo))) {
            File::delete(public_path('staff_images/' . $photo));
        }
    }

}
