<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;

/** Bitácora de auditoría (solo administrador). */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $audits = Audit::with('user')
            ->when($request->query('entidad'), fn ($q, $e) => $q->where('auditable_type', $e))
            ->when($request->query('accion'), fn ($q, $a) => $q->where('action', $a))
            ->when(trim((string) $request->query('q')), fn ($q, $s) => $q->whereHas(
                'user', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")
            ))
            ->latest('id')->paginate(25)->withQueryString();

        return view('admin.audits.index', compact('audits'));
    }
}
