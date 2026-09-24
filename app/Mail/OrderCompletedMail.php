<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderCompletedMail extends Mailable
{
    use SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $pdf = Pdf::loadView('invoices.invoice', ['order' => $this->order]);

        return $this->subject('Terima Kasih! Pesanan #' . $this->order->order_number . ' Telah Selesai')
            ->view('emails.order-completed')
            ->with([
                'order'    => $this->order,
                'orderUrl' => route('customer.order.show', $this->order->id),
            ])
            ->attachData($pdf->output(), 'Invoice-' . $this->order->order_number . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}