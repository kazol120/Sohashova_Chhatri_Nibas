<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Staffs;
use App\Models\User; 

class StaffSalaryPayment extends Model
{
    use HasFactory;

    protected $table = 'staff_salary_payments';

    protected $fillable = [
        'staff_id',
        'salary_month',
        'salary_year',
        'payment_type',
        'amount',
        'payment_date',
        'note',
        'created_by',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
        'salary_month' => 'integer',
        'salary_year' => 'integer',
        'status' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staffs::class, 'staff_id');
    }

    public function user(){

        return $this->belongsTo(User::class, 'created_by');

    }


}