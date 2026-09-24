<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'pelanggan_id',
        'product_id',
        'project_name',
        'design_notes',
        'reference_file_path',
        'final_mockup_path',
        'is_design_approved',
        'total_quantity',
        'biaya_sablon',
        'final_price',
        'status',
        'production_photo_path',
        'production_notes',
        'payment_confirmed_at',
        'completed_at',
        'payment_type',
        'dp_amount',
        'midtrans_snap_token',
        'midtrans_order_id',
        'payment_status',
    ];

    protected $casts = [
        'is_design_approved'   => 'boolean',
        'biaya_sablon'         => 'decimal:2',
        'final_price'          => 'decimal:2',
        'final_mockup_path'     => 'array',
        'production_photo_path' => 'array',
        'payment_confirmed_at' => 'datetime',
        'completed_at'         => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function chats()
    {
        return $this->hasMany(OrderChat::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diskusi_desain'       => 'Diskusi Desain',
            'menunggu_spesifikasi' => 'Menunggu Spesifikasi',
            'menunggu_estimasi'    => 'Menunggu Estimasi Harga',
            'menunggu_pembayaran'  => 'Menunggu Pembayaran',
            'diproses'             => 'Sedang Diproses',
            'siap_diambil'         => 'Siap Diambil / Dikirim',
            'selesai'              => 'Selesai',
            'dibatalkan'           => 'Dibatalkan',
            default                => ucfirst($this->status),
        };
    }
}
