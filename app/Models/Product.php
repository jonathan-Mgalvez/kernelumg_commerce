<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use Auditable;

    protected $fillable = [
        'category_id', 'sku', 'name', 'subtitle', 'standard', 'description',
        'price', 'unit', 'stock', 'min_stock', 'location', 'image', 'featured', 'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'featured' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /** La oferta vigente con mayor descuento (si hay varias). Cargar con ->with('activeOffer'). */
    public function activeOffer(): HasOne
    {
        return $this->hasOne(Offer::class)->vigente()->orderByDesc('discount_rate');
    }

    /** Precio con la oferta vigente aplicada (sin IVA). */
    public function getFinalPriceAttribute(): float
    {
        $rate = (float) ($this->activeOffer?->discount_rate ?? 0);

        return round((float) $this->price * (100 - $rate) / 100, 2);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /** URL de la imagen: puede ser una ruta de /public (seeders) o un archivo subido a storage. */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }
        if (Str::startsWith($this->image, ['http', '/'])) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    /** ok | bajo | agotado */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'agotado';
        }

        return $this->stock <= $this->min_stock ? 'bajo' : 'ok';
    }
}
