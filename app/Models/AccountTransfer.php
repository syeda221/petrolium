<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransfer extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function fromAccount()
    {
        return $this->belongsTo(\App\Models\Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(\App\Models\Account::class, 'to_account_id');
    }

    public static function generateInvoiceNo()
    {
        $last = self::orderBy('id', 'desc')->first();
        if (!$last) {
            return 'ATV-001';
        }
        $lastId = (int) substr($last->atvid, 4);
        return 'ATV-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
    }
}
