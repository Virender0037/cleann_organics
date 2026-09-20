<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Forward fulfilment steps only. Cancelling is deliberately not offered
     * here: it must also restore stock and settle payment, which is a
     * separate workflow.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:confirmed,packed,shipped,delivered'],
        ];
    }
}
