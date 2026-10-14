<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use Auditable;

    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'procesando' => 'Procesando',
        'despachado' => 'Despachado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    public const PAGOS = [
        'transferencia' => 'Transferencia bancaria',
        'deposito' => 'Depósito monetario',
        'contra_entrega' => 'Pago contra entrega',
    ];

    protected $fillable = [
        'user_id', 'quote_id', 'code', 'status', 'payment_method', 'shipping_address', 'notes',
        'subtotal', 'discount', 'shipping', 'tax', 'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusChipAttribute(): string
    {
        return match ($this->status) {
            'entregado' => 'chip-ok',
            'despachado', 'procesando' => 'chip-info',
            'pendiente' => 'chip-warn',
            'cancelado' => 'chip-danger',
            default => 'chip',
        };
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAGOS[$this->payment_method] ?? $this->payment_method;
    }
}
