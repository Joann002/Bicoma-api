<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
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
            'type' => ['sometimes', Rule::in(Item::TYPES)],
            'title' => ['sometimes', 'string', 'max:255'],
            'creator' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['sometimes', Rule::in(Item::STATUSES)],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'notes' => ['nullable', 'string'],
            'synopsis' => ['nullable', 'string'],
            'genre' => ['nullable', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'external_source' => ['nullable', 'string', 'max:255'],
            'finished_at' => ['nullable', 'date'],
        ];
    }
}
