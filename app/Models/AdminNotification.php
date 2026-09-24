<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\NewAdminNotification;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'icon',
        'color',
        'order_id',
        'from_user_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Helper: buat notifikasi + broadcast sekaligus
     */
    public static function send(string $type, string $title, string $message, array $extra = [])
    {
        $notif = self::create(array_merge([
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => self::iconFor($type),
            'color'   => self::colorFor($type),
        ], $extra));

        // Broadcast ke admin channel
        broadcast(new NewAdminNotification($notif))->toOthers();

        return $notif;
    }

    private static function iconFor(string $type): string
    {
        return match ($type) {
            'new_order'        => 'shopping_cart',
            'new_chat'         => 'chat',
            'design_approved'  => 'verified',
            'payment_dp'       => 'account_balance_wallet',
            'payment_lunas'    => 'payments',
            'spec_submitted'   => 'format_list_bulleted',
            'order_completed'  => 'check_circle',
            default            => 'notifications',
        };
    }

    private static function colorFor(string $type): string
    {
        return match ($type) {
            'new_order'        => 'blue',
            'new_chat'         => 'blue',
            'design_approved'  => 'green',
            'payment_dp'       => 'yellow',
            'payment_lunas'    => 'green',
            'spec_submitted'   => 'purple',
            'order_completed'  => 'green',
            default            => 'blue',
        };
    }
}
