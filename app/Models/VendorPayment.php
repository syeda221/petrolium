<?php

// app/Models/VendorPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    protected $fillable = [
        'vendor_id',
        'account_id',
        'admin_or_user_id',
        'payment_date',
        'amount',
        'adjustment_type',
        'payment_method',
        'note',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
