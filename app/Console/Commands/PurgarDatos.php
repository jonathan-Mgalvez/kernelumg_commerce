<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\Quote;
use Illuminate\Console\Command;

/** Mantenimiento diario: vence cotizaciones y limpia borradores y carritos abandonados. */
class PurgarDatos extends Command
{
    protected $signature = 'ferrotek:purgar {--dias=30 : Días de inactividad para borrar borradores y carritos}';

    protected $description = 'Marca como vencidas las cotizaciones caducadas y elimina borradores vacíos y carritos abandonados';

    public function handle(): int
    {
        $limite = now()->subDays((int) $this->option('dias'));

        // Uno por uno para que el cambio de estado quede en la auditoría.
        $vencidas = 0;
        Quote::whereIn('status', ['enviada', 'aprobada'])->whereDate('valid_until', '<', today())
            ->whereDoesntHave('order')
            ->each(function (Quote $q) use (&$vencidas) {
                $q->update(['status' => 'vencida']);
                $vencidas++;
            });

        $borradores = Quote::where('status', 'borrador')->whereDoesntHave('items')
            ->where('updated_at', '<', $limite)->delete();

        $carritos = Cart::where('updated_at', '<', $limite)->delete();

        $this->table(['Acción', 'Registros'], [
            ['Cotizaciones marcadas como vencidas', $vencidas],
            ['Borradores vacíos eliminados', $borradores],
            ['Carritos abandonados eliminados', $carritos],
        ]);

        return self::SUCCESS;
    }
}
