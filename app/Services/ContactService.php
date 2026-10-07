<?php

namespace App\Services;

use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    private const ADMIN_NOTIFICATION_EMAIL = 'admin@kernelumg.com';

    public function registerAndNotify(array $data): ContactMessage
    {
        // 1. Persistencia Inmediata en contact_messages
        $contactMessage = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'No Leído',
            'admin_notes' => null,
        ]);

        // 2. Despacho de Correo Notificatorio vía SMTP
        try {
            Mail::to(self::ADMIN_NOTIFICATION_EMAIL)->send(new NewContactMessageMail($contactMessage));
        } catch (\Exception $e) {
            logger()->error('Fallo al despachar notificación de contacto administrativo vía SMTP: ' . $e->getMessage());
        }

        return $contactMessage;
    }
}