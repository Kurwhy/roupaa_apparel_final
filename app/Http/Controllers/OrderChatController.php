<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\AdminNotification;
use App\Events\MessageSent;
use App\Models\CustomerNotification;

class OrderChatController extends Controller
{
    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized. Sesi berakhir.'], 401);
        }

        $isOwnerOrAdmin = in_array($user->role, ['admin', 'owner']);
        $isCustomerOwner = $user->pelanggan && $order->pelanggan_id === $user->pelanggan->id;

        if (!$isOwnerOrAdmin && !$isCustomerOwner) {
            if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json(['error' => 'Anda tidak berhak membalas chat di project ini.'], 403);
            }
            abort(403, 'Anda tidak berhak membalas chat di project ini.');
        }

        $request->validate([
            'message'         => 'nullable|string|max:5000',
            'attachment_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,ai,cdr|max:10240',
        ]);

        $trimmedMessage = trim($request->message ?? '');

        if (!$trimmedMessage && !$request->hasFile('attachment_file')) {
            if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json(['error' => 'Pesan atau file tidak boleh kosong.'], 422);
            }
            return back()->with('error', 'Pesan atau file tidak boleh kosong.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment_file')) {
            $file = $request->file('attachment_file');
            $safeName = preg_replace('/[^a-zA-Z0-9_.\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME));
            $filename = time() . '_' . $safeName;
            $attachmentPath = $file->storeAs('chat_attachments', $filename, 'public');
        }

        $chat = $order->chats()->create([
            'user_id'           => $user->id,
            'message'           => $trimmedMessage ?: null,
            'attachment_path'   => $attachmentPath,
            'is_system_message' => false,
        ]);

        broadcast(new MessageSent($chat->load('user')))->toOthers();

        if (in_array($user->role, ['admin', 'owner'])) {
            $order->load('pelanggan');
            if ($order->pelanggan) {
                CustomerNotification::send(
                    userId: $order->pelanggan->user_id,
                    type: 'new_message',
                    title: 'Pesan Baru',
                    message: 'Admin mengirim pesan di pesanan #' . $order->order_number,
                    extra: ['order_id' => $order->id],
                );
            }
        }

        if ($user->role === 'pelanggan') {
            $order->load('pelanggan');
            AdminNotification::send(
                'new_chat',
                'Pesan Baru dari Customer',
                "{$order->pelanggan->nama_lengkap} mengirim pesan di \"{$order->project_name}\".",
                ['order_id' => $order->id, 'from_user_id' => $user->id]
            );
        }

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'chat' => $chat
            ]);
        }

        return back();
    }

    public function markAsRead($orderId)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = Order::findOrFail($orderId);

        $isOwnerOrAdmin = in_array($user->role, ['admin', 'owner']);
        $isCustomerOwner = $user->pelanggan && $order->pelanggan_id === $user->pelanggan->id;

        if (!$isOwnerOrAdmin && !$isCustomerOwner) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $userId = $user->id;

        $unreadMessages = $order->chats()
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($unreadMessages > 0) {
            broadcast(new \App\Events\MessagesRead($orderId, $userId))->toOthers();
        }

        return response()->json(['status' => 'success']);
    }
}
