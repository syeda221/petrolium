<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Stock;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    
    // Auto-generate item_code if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // If no item_code provided, generate one
            if (empty($model->item_code)) {
                $model->item_code = 'P' . str_pad($model->id ?? 0, 6, '0', STR_PAD_LEFT);
            }
        });

        static::created(function ($model) {
            // If auto-generated code looks temporary, update it with proper ID
            if (strpos($model->item_code, 'P0000') === 0) {
                $model->update(['item_code' => 'P' . str_pad($model->id, 6, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function activeDiscount()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id')
            ->where('status', 1); // only active discount
    }

    public function discountProduct()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id', 'id')
            ->where('status', 1);
    }

    public function category_relation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sub_category_relation()
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }


    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function stock()
    {
        return $this->hasOne(Stock::class);
    }
}
