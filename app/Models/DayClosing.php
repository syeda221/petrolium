<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'opening_balance',
        'total_in',
        'total_out',
        'closing_balance',
        'is_closed',
        'closed_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean'
    ];
}
