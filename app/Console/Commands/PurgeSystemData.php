<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\Quotation;
use Illuminate\Console\Command;

class PurgeSystemData extends Command
{
    protected $signature = 'system:purge-stale-data';
    protected $description = 'Purga cotizaciones caducadas y carritos temporales de visitantes obsoletos';

    public function handle(): int
    {
        $this->info('Iniciando depuración de datos temporales...');

        // 1. Eliminar cotizaciones vencidas no convertidas hace más de 15 días
        $purgedQuotes = Quotation::where('is_converted', false)
            ->where('expires_at', '<', now()->subDays(15))
            ->delete();
        $this->line("Cotizaciones expiradas depuradas: {$purgedQuotes}");

        // 2. Eliminar carritos huérfanos de visitantes (sin usuario) inactivos por más de 30 días
        $purgedCarts = Cart::whereNull('user_id')
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
        $this->line("Carritos de visitantes obsoletos eliminados: {$purgedCarts}");

        $this->info('Depuración completada exitosamente.');

        return self::SUCCESS;
    }
}