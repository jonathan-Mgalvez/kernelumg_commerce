<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class MessageController extends Controller
{
    public function index()
    {
        return view('admin.messages.index', [
            'messages' => ContactMessage::latest()->paginate(15),
        ]);
    }

    public function show(ContactMessage $message)
    {
        $message->update(['read' => true]);

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Mensaje eliminado.');
    }
}
