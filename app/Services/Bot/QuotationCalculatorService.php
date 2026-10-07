<?php

namespace App\Services\Bot;

use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationCalculatorService
{
    private const TAX_RATE = 0.12;

    public function calculateAndPersist(array $items, string $sessionToken, ?int $userId = null): array
    {
        return DB::transaction(function () use ($items, $sessionToken, $userId) {
            $breakdown = [];
            $netSubtotal = 0.00;
            $totalDiscount = 0.00;

            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('is_active', true)
                    ->with('activeOffer')
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ["El producto ID {$item['product_id']} no se encuentra activo o no existe en el catálogo."]
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $basePrice = (float) $product->price;
                $discountPercentage = 0.00;
                $unitDiscount = 0.00;

                if ($product->activeOffer) {
                    $discountPercentage = (float) $product->activeOffer->discount_rate;
                    $unitDiscount = round($basePrice * ($discountPercentage / 100), 2);
                }

                $effectiveUnitPrice = $basePrice - $unitDiscount;
                $lineSubtotal = round($effectiveUnitPrice * $quantity, 2);
                $lineDiscountTotal = round($unitDiscount * $quantity, 2);

                $netSubtotal += $lineSubtotal;
                $totalDiscount += $lineDiscountTotal;

                $breakdown[] = [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'base_price' => $basePrice,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $lineDiscountTotal,
                    'subtotal' => $lineSubtotal
                ];
            }

            $taxAmount = round($netSubtotal * self::TAX_RATE, 2);
            $totalConsolidated = round($netSubtotal + $taxAmount, 2);

            $quotation = Quotation::create([
                'session_token' => $sessionToken,
                'user_id' => $userId,
                'total' => $totalConsolidated,
                'expires_at' => now()->addDays(7),
                'is_converted' => false,
            ]);

            foreach ($breakdown as $line) {
                QuotationDetail::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $line['product_id'],
                    'base_price' => $line['base_price'],
                    'discount_amount' => $line['discount_amount'],
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            return [
                'quotation_id' => $quotation->id,
                'session_token' => $sessionToken,
                'expires_at' => $quotation->expires_at->toDateTimeString(),
                'currency' => 'GTQ',
                'breakdown' => $breakdown,
                'financial_summary' => [
                    'net_subtotal' => $netSubtotal,
                    'total_discount' => $totalDiscount,
                    'tax_amount' => $taxAmount,
                    'total' => $totalConsolidated
                ]
            ];
        });
    }
}
