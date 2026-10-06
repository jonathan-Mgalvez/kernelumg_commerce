<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $table = 'quotations';

    protected $fillable = [
        'session_token',
        'user_id',
        'total',
        'expires_at',
        'is_converted',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_converted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(QuotationDetail::class, 'quotation_id');
    }
}