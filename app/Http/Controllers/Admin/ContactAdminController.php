<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactStatusRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(15)->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function show(int $id): View
    {
        $message = ContactMessage::findOrFail($id);

        if ($message->status === 'No Leído') {
            $message->update(['status' => 'Leído']);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function updateStatus(ContactStatusRequest $request, int $id): RedirectResponse
    {
        $message = ContactMessage::findOrFail($id);
        $message->update($request->validated());

        return redirect()->route('admin.messages.show', $message->id)
            ->with('success', 'El estado del mensaje y las notas administrativas han sido guardadas.');
    }
}