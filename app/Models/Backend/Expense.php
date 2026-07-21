<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'expense_category',
        'expense_note',
        'expense_amount',
    ];

      public function expensetype()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_category');
    }
}
