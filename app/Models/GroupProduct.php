<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function components()
    {
        return $this->hasMany(GroupProductComponent::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Automatically deactivate when stock reaches 0
    public function checkAndDeactivate()
    {
        if ($this->current_stock <= 0 && $this->is_active) {
            $this->update(['is_active' => false]);
        }
    }
}
