<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'product_id');
    }

    public function activeOffer(): HasOne
    {
        return $this->hasOne(Offer::class, 'product_id')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latestOfMany();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'product_id');
    }

    public function quotationDetails(): HasMany
    {
        return $this->hasMany(QuotationDetail::class, 'product_id');
    }
    /**
     * Scope para consultas filtradas aprovechando índices compuestos.
     */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query->where('is_active', true)
            ->when($filters['category'] ?? null, function ($q, $cat) {
                $q->whereHas('category', fn($c) => $c->where('slug', $cat));
            })
            ->when($filters['min_price'] ?? null, fn($q, $min) => $q->where('price', '>=', (float)$min))
            ->when($filters['max_price'] ?? null, fn($q, $max) => $q->where('price', '<=', (float)$max))
            ->when(($filters['stock'] ?? null) === 'in_stock', fn($q) => $q->where('stock', '>', 0));
    }
}