<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\NewCustomerNotification;

class CustomerNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'order_id',
        'is_read',
    ];

    protected $casts = ['is_read' => 'boolean'];

    public static function send(int $userId, string $type, string $title, string $message, array $extra = [])
    {
        $notif = self::create(array_merge([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => self::iconFor($type),
            'color'   => self::colorFor($type),
        ], $extra));

        broadcast(new NewCustomerNotification(
            userId: $userId,
            type: $type,
            title: $title,
            message: $message,
            icon: $notif->icon,
            color: $notif->color,
            orderId: $notif->order_id,
            notifId: $notif->id,
        ));

        return $notif;
    }

    private static function iconFor(string $type): string
    {
        return match ($type) {
            'new_message'        => 'chat',
            'mockup_uploaded'    => 'image',
            'status_update'      => 'assignment',
            'payment_required'   => 'payments',
            'production_complete' => 'inventory_2',
            'order_complete'     => 'task_alt',
            default              => 'notifications',
        };
    }

    private static function colorFor(string $type): string
    {
        return match ($type) {
            'new_message'        => 'blue',
            'mockup_uploaded'    => 'purple',
            'payment_required'   => 'yellow',
            'production_complete' => 'green',
            'order_complete'     => 'green',
            default              => 'blue',
        };
    }
}
