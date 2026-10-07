<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminQuotationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Quotation::with(['user', 'details.product']);

        if ($request->filled('converted')) {
            $isConverted = $request->boolean('converted');
            $query->where('is_converted', $isConverted);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('session_token', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $quotations = $query->latest()->paginate(15)->withQueryString();

        return view('admin.quotations.index', compact('quotations'));
    }

    public function show(int $id): View
    {
        $quotation = Quotation::with(['user', 'details.product'])->findOrFail($id);

        return view('admin.quotations.show', compact('quotation'));
    }
}