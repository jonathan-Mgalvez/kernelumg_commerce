<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use Auditable;

    protected $fillable = ['product_id', 'title', 'discount_rate', 'starts_at', 'ends_at', 'active'];

    protected function casts(): array
    {
        return [
            'discount_rate' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Ofertas activas cuyo periodo incluye este momento. */
    public function scopeVigente(Builder $query): void
    {
        $query->where('active', true)->where('starts_at', '<=', now())->where('ends_at', '>=', now());
    }

    /** vigente | programada | vencida | inactiva */
    public function getEstadoAttribute(): string
    {
        return match (true) {
            ! $this->active => 'inactiva',
            $this->starts_at->isFuture() => 'programada',
            $this->ends_at->isPast() => 'vencida',
            default => 'vigente',
        };
    }

    public function getEstadoChipAttribute(): string
    {
        return match ($this->estado) {
            'vigente' => 'chip-ok',
            'programada' => 'chip-info',
            'vencida' => 'chip-danger',
            default => 'chip',
        };
    }
}
