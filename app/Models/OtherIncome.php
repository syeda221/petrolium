<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'title',
        'account_id',
        'amount',
        'remarks',
        'admin_or_user_id',
    ];
}
