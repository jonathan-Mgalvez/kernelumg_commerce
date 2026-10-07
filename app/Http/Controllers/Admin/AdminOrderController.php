<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request): View
    {
        $query = Order::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(int $id): View
    {
        $order = Order::with(['user', 'details.product'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(OrderStatusRequest $request, int $id): RedirectResponse
    {
        $order = Order::with('user')->findOrFail($id);
        $oldValues = $order->toArray();
        $newStatus = $request->validated('status');

        $order->update([
            'status' => $newStatus,
        ]);

        $this->auditService->record('CAMBIO_ESTADO_PEDIDO', $order, $oldValues, ['status' => $newStatus]);

        try {
            Mail::to($order->user->email)->send(new OrderStatusUpdatedMail($order));
        } catch (\Exception $e) {
            logger()->error('Error de despacho SMTP al actualizar estado de orden: ' . $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', "El pedido ha sido actualizado al estado: {$newStatus}.");
    }
}