<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderNotificationMail extends Mailable
{
    use SerializesModels;

    public Order $order;
    public string $headline;
    public string $bodyMessage;

    public function __construct(Order $order, string $headline, string $bodyMessage)
    {
        $this->order = $order;
        $this->headline = $headline;
        $this->bodyMessage = $bodyMessage;
    }

    public function build()
    {
        return $this->subject($this->headline)
            ->view('emails.order-notification')
            ->with([
                'order'       => $this->order,
                'headline'    => $this->headline,
                'bodyMessage' => $this->bodyMessage,
                'orderUrl'    => route('customer.order.show', $this->order->id),
            ]);
    }
}