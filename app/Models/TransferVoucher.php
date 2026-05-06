<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'tvid',
        'transfer_date',
        'customer_id',
        'vendor_id',
        'source_party_type',
        'source_party_id',
        'destination_party_type',
        'destination_party_id',
        'amount',
        'remarks',
        'created_by'
    ];

    public static function generateInvoiceNo()
    {
        $lastRecord = self::latest('id')->first();
        $nextId = $lastRecord ? $lastRecord->id + 1 : 1;
        return 'TVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }
}
