<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Bitácora: quién cambió qué, cuándo y desde dónde. La usan los modelos con el trait Auditable. */
class AuditoriaService
{
    public function registrar(string $accion, Model $model, ?array $antes = null, ?array $despues = null): Audit
    {
        $request = app()->runningInConsole() ? null : request();

        return Audit::create([
            'user_id' => Auth::id(),
            'action' => $accion,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $antes ?: null,
            'new_values' => $despues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : 'consola',
        ]);
    }
}
