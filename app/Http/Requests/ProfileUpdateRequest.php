<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Phase J: the only field added to the customer-editable set.
            // role / status / provider / provider_id / email_verified_at /
            // avatar are deliberately absent — validated() never returns
            // them, so ProfileController::update()'s fill() can't touch them.
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
