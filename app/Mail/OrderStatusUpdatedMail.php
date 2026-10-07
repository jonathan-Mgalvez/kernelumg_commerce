<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización de Pedido #' . $this->order->tracking_code . ': ' . $this->order->status,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_status_updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}