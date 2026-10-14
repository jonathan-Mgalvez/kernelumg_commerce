<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('quote_id')->nullable()->unique()->constrained()->nullOnDelete(); // si viene de una cotización aprobada
            $table->string('code', 20)->unique();                // PED-2026-0001
            $table->string('status', 20)->default('pendiente');  // pendiente | procesando | despachado | entregado | cancelado
            $table->string('payment_method', 30);                // transferencia | deposito | contra_entrega
            $table->string('shipping_address');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);              // precio de lista congelado al comprar
            $table->decimal('discount_rate', 5, 2)->default(0); // % de oferta o de volumen aplicado
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
