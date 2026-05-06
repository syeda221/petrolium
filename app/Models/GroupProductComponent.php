<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupProductComponent extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function groupProduct()
    {
        return $this->belongsTo(GroupProduct::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
