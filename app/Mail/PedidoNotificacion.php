<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Correo al cliente cuando se crea su pedido ($tipo = confirmado) o cambia de estado ($tipo = actualizado). */
class PedidoNotificacion extends Mailable
{
    public function __construct(public Order $order, public string $tipo = 'confirmado') {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->tipo === 'confirmado'
            ? "Recibimos tu pedido {$this->order->code} · FerroTek"
            : "Tu pedido {$this->order->code} está {$this->order->status_label} · FerroTek");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pedido');
    }
}
