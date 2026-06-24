<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'borrower_name' => ['required', 'string', 'max:255'],
            'loan_date' => ['nullable', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
        ];
    }
}
