<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = ['quote_id', 'product_id', 'quantity', 'unit_price', 'discount_rate', 'amount'];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
