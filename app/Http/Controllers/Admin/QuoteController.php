<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::with('user')->withCount('items')
            ->where('status', '!=', 'borrador')
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
            ->when(trim((string) $request->query('q')), fn ($q, $s) => $q->where(
                fn ($w) => $w->where('code', 'like', "%$s%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"))
            ))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    public function show(Quote $quote)
    {
        return view('admin.quotes.show', ['quote' => $quote->load('items.product', 'user')]);
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Quote::STATUSES))],
        ]);
        $quote->update($data);

        return back()->with('status', "Estado de {$quote->code} cambiado a {$quote->status_label}.");
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();

        return redirect()->route('admin.quotes.index')->with('status', 'Cotización eliminada.');
    }
}
