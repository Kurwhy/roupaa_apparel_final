<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Pelanggan;
use App\Models\OrderChat;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Ambil notifikasi terbaru + unread count
     */
    public function index(Request $request)
    {
        $notifications = AdminNotification::latest()
            ->take(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'icon'       => $n->icon,
                    'color'      => $n->color,
                    'order_id'   => $n->order_id,
                    'is_read'    => $n->is_read,
                    'created_at' => $n->created_at->diffForHumans(),
                    'time'       => $n->created_at->format('H:i'),
                ];
            });

        $unreadCount = AdminNotification::where('is_read', false)->count();

        $totalCustomers = Pelanggan::count();

        $unreadChats = OrderChat::whereNull('read_at')
            ->whereHas('user', function ($q) {
                $q->where('role', 'pelanggan');
            })
            ->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'notifications'  => $notifications,
                'unread_count'   => $unreadCount,
                'total_customers' => $totalCustomers,
                'unread_chats'   => $unreadChats,
            ],
        ]);
    }

    public function markAsRead($id)
    {
        AdminNotification::where('id', $id)->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead()
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function markReadByOrder($orderId)
    {
        AdminNotification::where('order_id', $orderId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }
}
