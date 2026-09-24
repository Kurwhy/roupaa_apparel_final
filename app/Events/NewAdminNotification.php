<?php

namespace App\Events;

use App\Models\AdminNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewAdminNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $notification;

    public function __construct(AdminNotification $notif)
    {
        $this->notification = [
            'id'         => $notif->id,
            'type'       => $notif->type,
            'title'      => $notif->title,
            'message'    => $notif->message,
            'icon'       => $notif->icon,
            'color'      => $notif->color,
            'order_id'   => $notif->order_id,
            'is_read'    => $notif->is_read,
            'created_at' => $notif->created_at->format('H:i'),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }
}
