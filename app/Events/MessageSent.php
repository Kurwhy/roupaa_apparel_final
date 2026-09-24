<?php

namespace App\Events;

use App\Models\OrderChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(OrderChat $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.chat.' . $this->message->order_id),
        ];
    }

    public function broadcastAs()
    {
        return 'message.new';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => array_merge($this->message->toArray(), [
                'user' => [
                    'id'   => $this->message->user?->id,
                    'role' => $this->message->user?->role,
                ],
            ]),
        ];
    }
}
