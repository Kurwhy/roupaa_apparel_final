<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockBatch extends Model
{
    protected $fillable = [
        'invoice_number',
        'items_data',
        'status',
        'total_pengeluaran',
        'struk_path',
        'verified_by',
    ];

    protected $casts = [
        'items_data' => 'array',
    ];

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
