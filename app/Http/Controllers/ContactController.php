<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    protected ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index(): View
    {
        return view('contact.index');
    }

    public function send(ContactMessageRequest $request): RedirectResponse
    {
        $this->contactService->registerAndNotify($request->validated());

        return redirect()->route('contact.index')
            ->with('success', 'Su mensaje ha sido enviado exitosamente. Nos pondremos en contacto a la brevedad.');
    }
}