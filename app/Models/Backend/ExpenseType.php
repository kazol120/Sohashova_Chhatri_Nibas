<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name',
    ];


       public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_category');
    }

}
