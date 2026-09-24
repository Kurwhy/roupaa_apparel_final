<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewCustomerNotification implements ShouldBroadcastNow
{
    public function __construct(
        public int    $userId,
        public string $type,
        public string $title,
        public string $message,
        public string $icon    = 'notifications',
        public string $color   = 'blue',
        public ?int   $orderId = null,
        public ?int   $notifId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('customer.notifications.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'NewCustomerNotification';
    }

    public function broadcastWith(): array
    {
        return [
            'id'       => $this->notifId,
            'type'     => $this->type,
            'title'    => $this->title,
            'message'  => $this->message,
            'icon'     => $this->icon,
            'color'    => $this->color,
            'order_id' => $this->orderId,
            'is_read'  => false,
        ];
    }
}
