<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array',  'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1']
        ];
    }

    protected function prepareForValidation()
    {
        $items = collect($this->input('items', []))
            ->groupBy('id')
            ->map(function ($group) {
                return [
                    'id' => $group->first()['id'],
                    'quantity' => $group->sum('quantity')
                ];
            })
            ->values()
            ->all();

        $this->merge([
            'items' => $items
        ]);
    }
}
