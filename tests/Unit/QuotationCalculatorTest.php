<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Bot\QuotationCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcula_correctamente_subtotales_descuentos_e_iva(): void
    {
        $service = new QuotationCalculatorService();

        $category = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'LAP-TEST-100',
            'name' => 'Laptop de Prueba',
            'slug' => 'laptop-de-prueba',
            'description' => 'Equipo para pruebas de cotizador',
            'price' => 100.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        Offer::create([
            'product_id' => $product->id,
            'discount_rate' => 10.00,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
            'is_active' => true,
        ]);

        $items = [
            ['product_id' => $product->id, 'quantity' => 2]
        ];

        $result = $service->calculateAndPersist($items, 'sess_test_123', null);

        // Subtotal unitario con 10% OFF: 90.00 * 2 = 180.00
        $this->assertEquals(180.00, $result['financial_summary']['net_subtotal']);
        $this->assertEquals(20.00, $result['financial_summary']['total_discount']);
        // IVA 12% sobre 180.00 = 21.60
        $this->assertEquals(21.60, $result['financial_summary']['tax_amount']);
        // Total: 180.00 + 21.60 = 201.60
        $this->assertEquals(201.60, $result['financial_summary']['total']);
        $this->assertDatabaseHas('quotations', ['session_token' => 'sess_test_123']);
    }
}