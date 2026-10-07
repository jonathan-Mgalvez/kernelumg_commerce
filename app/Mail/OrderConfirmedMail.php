<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['details.product', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Pedido #' . $this->order->tracking_code . ' - KernelUMG Commerce',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}