<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    use Auditable;

    /** Solo se audita el cambio de estado (no cada recálculo del borrador). */
    protected array $auditarSolo = ['status'];

    public const STATUSES = [
        'borrador' => 'Borrador',
        'enviada' => 'Enviada',
        'aprobada' => 'Aprobada',
        'rechazada' => 'Rechazada',
        'vencida' => 'Vencida',
    ];

    protected $fillable = [
        'user_id', 'code', 'status', 'delivery_address', 'notes',
        'subtotal', 'discount', 'shipping', 'tax', 'total', 'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /** Aprobada, vigente y todavía sin pedido: el cliente la puede convertir en compra. */
    public function puedeConvertirse(): bool
    {
        return $this->status === 'aprobada'
            && (! $this->valid_until || $this->valid_until->endOfDay()->isFuture())
            && ! $this->order()->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** Clase CSS del chip según estado. */
    public function getStatusChipAttribute(): string
    {
        return match ($this->status) {
            'aprobada' => 'chip-ok',
            'enviada' => 'chip-info',
            'rechazada', 'vencida' => 'chip-danger',
            default => 'chip',
        };
    }
}
