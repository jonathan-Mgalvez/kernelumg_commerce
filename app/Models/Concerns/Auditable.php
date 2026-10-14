<?php

namespace App\Models\Concerns;

use App\Services\AuditoriaService;
use Illuminate\Support\Arr;

/**
 * Registra en la tabla audits cada alta, cambio y baja del modelo.
 * Un modelo puede limitar qué campos disparan el registro con: protected array $auditarSolo = ['status'];
 * (en ese caso tampoco se registra la creación).
 */
trait Auditable
{
    /** Campos que nunca se guardan en la bitácora. */
    private static array $auditIgnorar = ['password', 'remember_token', 'created_at', 'updated_at'];

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            if (isset($model->auditarSolo)) {
                return; // ej. cotizaciones: el borrador se crea solo; interesa cuando cambia de estado
            }
            app(AuditoriaService::class)->registrar('creado', $model, null, $model->valoresAuditables($model->getAttributes()));
        });

        static::updated(function ($model) {
            $cambios = $model->valoresAuditables($model->getChanges());
            if (isset($model->auditarSolo) && ! Arr::hasAny($cambios, $model->auditarSolo)) {
                return;
            }
            if ($cambios) {
                $antes = Arr::only($model->getRawOriginal(), array_keys($cambios));
                app(AuditoriaService::class)->registrar('actualizado', $model, $antes, $cambios);
            }
        });

        static::deleted(function ($model) {
            app(AuditoriaService::class)->registrar('eliminado', $model, $model->valoresAuditables($model->getAttributes()));
        });
    }

    public function valoresAuditables(array $valores): array
    {
        return Arr::except($valores, self::$auditIgnorar);
    }
}
