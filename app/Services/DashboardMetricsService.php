<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    public function getExecutiveSummary(): array
    {
        $totalSales = (float) Order::where('status', '!=', 'Cancelado')->sum('total');
        $ordersCount = Order::count();
        $pendingOrders = Order::where('status', 'Pendiente')->count();
        $outOfStockCount = Product::where('stock', 0)->where('is_active', true)->count();
        $unreadMessages = ContactMessage::where('status', 'No Leído')->count();

        $totalQuotations = Quotation::count();
        $convertedQuotations = Quotation::where('is_converted', true)->count();
        $conversionRate = $totalQuotations > 0 
            ? round(($convertedQuotations / $totalQuotations) * 100, 2) 
            : 0.00;

        return [
            'total_sales' => $totalSales,
            'orders_count' => $ordersCount,
            'pending_orders' => $pendingOrders,
            'out_of_stock_count' => $outOfStockCount,
            'unread_messages' => $unreadMessages,
            'total_quotations' => $totalQuotations,
            'converted_quotations' => $convertedQuotations,
            'conversion_rate' => $conversionRate,
        ];
    }

    public function getOrderStatusBreakdown(): array
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}