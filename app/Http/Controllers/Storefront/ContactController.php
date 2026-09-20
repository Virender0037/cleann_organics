<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

/**
 * Saves the public Contact Us form as a ContactMessage — the same records
 * the admin's inbox (Admin → CMS → Contact Messages) already lists, marks
 * read and replies to. Status starts at the column default ('unread').
 */
class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']));

        return redirect()
            ->to(route('contact').'#contact-form')
            ->with('contact_success', 'Thank you — your message has been sent. Our team will get back to you soon.');
    }
}
