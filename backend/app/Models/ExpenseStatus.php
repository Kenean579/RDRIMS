<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseStatus extends Model
{
    protected $table = 'expense_statuses';
    protected $fillable = ['name', 'label'];
}
