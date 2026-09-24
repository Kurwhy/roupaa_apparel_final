<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;

class CustomerNotificationController extends Controller
{
    public function index()
    {
        $userId = auth('api')->id();

        $notifs = CustomerNotification::where('user_id', $userId)
            ->latest()
            ->take(30)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'order_id'   => $n->order_id,
                'is_read'    => $n->is_read,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        $unread = CustomerNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'notifications' => $notifs,
                'unread_count'  => $unread,
            ],
        ]);
    }

    public function markRead($id)
    {
        CustomerNotification::where('id', $id)
            ->where('user_id', auth('api')->id())
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function markAllRead()
    {
        CustomerNotification::where('user_id', auth('api')->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function markReadByOrder($orderId)
    {
        CustomerNotification::where('user_id', auth('api')->id())
            ->where('order_id', $orderId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }
}
