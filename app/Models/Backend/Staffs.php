<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staffs extends Model
{

    use HasFactory;

    protected $table = 'staffs';

    protected $fillable = [

        'employee_id',
        'name',
        'phone',
        'email',
        'nid_passport',
        'gender',
        'date_of_birth',
        'division_id',
        'district_id',
        'thana_id',
        'permanent_address',
        'designation',
        'department',
        'salary',
        'joining_date',
        'shift_time',
        'status',
        'photo',
        'notes',
        'password',

    ];



    public function attendances()
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }
    

 public function thana()
    {
        return $this->belongsTo(Thana::class)->withDefault([
            'name' => '-'
        ]);
    }


    public function district()
    {
        return $this->belongsTo(District::class)->withDefault(['name' => '-']);
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->withDefault(['name' => '-']);
    }


        protected $appends = ['image_url'];


    public function getImageUrlAttribute(): ?string
    {
        if ($this->photo && file_exists(public_path('staff_images/' . $this->photo))) {
            return asset('staff_images/' . $this->photo);
        }
        return null;
    }


//     public function salaryPayments()
// {
//     return $this->hasMany(\App\Models\StaffSalaryPayment::class, 'staff_id');
// }


}
