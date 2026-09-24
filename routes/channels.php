<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Order;


Broadcast::channel('order.chat.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (!$order)
        return false;

    if (in_array($user->role, ['admin', 'owner'])) {
        return true;
    }

    if ($user->pelanggan && $order->pelanggan_id === $user->pelanggan->id) {
        return true;
    }

    return false;
});

Broadcast::channel('admin.notifications', function ($user) {
    return in_array($user->role, ['admin', 'owner']);
});
Broadcast::channel('customer.notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});