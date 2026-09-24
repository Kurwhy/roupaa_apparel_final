<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStateUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $action;

    public function __construct($orderId, $action)
    {
        $this->orderId = $orderId;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('order.chat.' . $this->orderId);
    }

    public function broadcastAs()
    {
        return 'order.state.updated';
    }
}