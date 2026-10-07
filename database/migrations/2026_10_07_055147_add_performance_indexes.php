<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Optimización en Catálogo de Productos (Filtros multifactoriales y bloqueos transaccionales)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'is_active', 'stock'], 'idx_products_cat_active_stock');
            $table->index(['is_active', 'price'], 'idx_products_active_price');
        });

        // 2. Optimización en Órdenes de Compra (Filtros administrativos y búsqueda rápida por tracking)
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
        });

        // 3. Optimización en Cotizaciones del Asistente Virtual
        Schema::table('quotations', function (Blueprint $table) {
            $table->index(['session_token', 'is_converted', 'expires_at'], 'idx_quotes_token_conv_exp');
        });

        // 4. Optimización en Ofertas y Liquidaciones Temporales
        Schema::table('offers', function (Blueprint $table) {
            $table->index(['product_id', 'is_active', 'start_date', 'end_date'], 'idx_offers_active_dates');
        });

        // 5. Optimización en Bitácora de Auditoría
        Schema::table('audits', function (Blueprint $table) {
            $table->index(['table_name', 'record_id'], 'idx_audits_target_record');
            $table->index(['user_id', 'action'], 'idx_audits_user_action');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_cat_active_stock');
            $table->dropIndex('idx_products_active_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_date');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex('idx_quotes_token_conv_exp');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('idx_offers_active_dates');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex('idx_audits_target_record');
            $table->dropIndex('idx_audits_user_action');
        });
    }
};