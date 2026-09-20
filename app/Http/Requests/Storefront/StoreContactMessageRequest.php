<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{6,30}$/'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            // Honeypot: real visitors never see or fill this field.
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'message.min' => 'Please tell us a little more (at least 10 characters).',
        ];
    }
}
