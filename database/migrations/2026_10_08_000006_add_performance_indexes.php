<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Índices para las consultas más frecuentes del catálogo, paneles y auditoría. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'active', 'stock'], 'idx_products_cat_active_stock');
            $table->index(['active', 'price'], 'idx_products_active_price');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_quotes_user_status');
            $table->index(['status', 'created_at'], 'idx_quotes_status_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->index(['product_id', 'active', 'starts_at', 'ends_at'], 'idx_offers_active_dates');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->index(['auditable_type', 'auditable_id'], 'idx_audits_target');
            $table->index(['user_id', 'action'], 'idx_audits_user_action');
        });
    }

    public function down(): void
    {
        // MySQL descarta el índice automático de una llave foránea cuando otro índice compuesto la cubre,
        // así que antes de borrar el compuesto se vuelve a crear un índice simple para esa llave.
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->dropIndex('idx_products_cat_active_stock');
            $table->dropIndex('idx_products_active_price');
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropIndex('idx_quotes_user_status');
            $table->dropIndex('idx_quotes_status_date');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_date');
        });
        Schema::table('offers', function (Blueprint $table) {
            $table->index('product_id');
            $table->dropIndex('idx_offers_active_dates');
        });
        Schema::table('audits', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropIndex('idx_audits_target');
            $table->dropIndex('idx_audits_user_action');
        });
    }
};
