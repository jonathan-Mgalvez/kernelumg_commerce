<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = Audit::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('table_name')) {
            $query->where('table_name', $request->input('table_name'));
        }

        $audits = $query->latest()->paginate(20)->withQueryString();

        return view('admin.audits.index', compact('audits'));
    }
}