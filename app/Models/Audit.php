<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    public const UPDATED_AT = null;

    /** Nombre legible de cada modelo auditado. */
    public const ENTIDADES = [
        Product::class => 'Producto',
        Category::class => 'Categoría',
        Offer::class => 'Oferta',
        Order::class => 'Pedido',
        Quote::class => 'Cotización',
        User::class => 'Usuario',
    ];

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEntidadAttribute(): string
    {
        return self::ENTIDADES[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    public function getActionChipAttribute(): string
    {
        return match ($this->action) {
            'creado' => 'chip-ok',
            'eliminado' => 'chip-danger',
            default => 'chip-info',
        };
    }

    /** Lista [campo => [antes, después]] para mostrar en el panel. */
    public function getCambiosAttribute(): array
    {
        $campos = array_unique([...array_keys($this->old_values ?? []), ...array_keys($this->new_values ?? [])]);

        return collect($campos)->mapWithKeys(fn ($c) => [$c => [
            $this->old_values[$c] ?? null,
            $this->new_values[$c] ?? null,
        ]])->all();
    }
}
