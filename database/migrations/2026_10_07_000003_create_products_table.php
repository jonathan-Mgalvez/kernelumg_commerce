<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku', 30)->unique();
            $table->string('name');
            $table->string('subtitle')->nullable();       // ej. "Grado 60 · Siderúrgica Certificada"
            $table->string('standard', 50)->nullable();   // norma: ASTM A615, AWS A5.1...
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('unit', 20)->default('unidad'); // unidad, caja, metro...
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('min_stock')->default(0);
            $table->string('location', 50)->nullable();   // ubicación en almacén
            $table->string('image')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
