<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeleteRequest;
use App\Http\Requests\Admin\BulkInventoryRequest;
use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BulkInventoryController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Actualiza masivamente precios y stock dentro de una transacción única.
     */
    public function updateBulk(BulkInventoryRequest $request): JsonResponse|RedirectResponse
    {
        $items = $request->validated('products');

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);
                $oldValues = $product->toArray();

                $product->update([
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                ]);

                $this->auditService->record('ACTUALIZACION_MASIVA', $product, $oldValues, $product->fresh()->toArray());
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Actualización masiva de inventario y precios efectuada con éxito.'
            ], 200);
        }

        return redirect()->back()->with('success', 'Inventario y tarifas actualizados exitosamente.');
    }

    /**
     * Ejecuta borrado lógico masivo (Soft Delete) garantizando integridad referencial histórica.
     */
    public function destroyBulk(BulkDeleteRequest $request): JsonResponse|RedirectResponse
    {
        $productIds = $request->validated('product_ids');

        DB::transaction(function () use ($productIds) {
            $products = Product::whereIn('id', $productIds)->get();

            foreach ($products as $product) {
                $oldValues = $product->toArray();
                $product->delete(); // Dispara soft delete si el modelo usa el trait
                $this->auditService->record('BORRADO_LOGICO_MASIVO', $product, $oldValues, ['deleted_at' => now()]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Los productos seleccionados fueron inactivados correctamente.'
            ], 200);
        }

        return redirect()->back()->with('success', 'Productos inactivados correctamente del catálogo activo.');
    }
}