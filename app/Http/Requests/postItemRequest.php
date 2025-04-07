<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $itemId = $this->route('post_management');
        if (! $itemId && $this->route('id')) {
            $itemId = $this->route('id');
        }

        $rules = [
            'item_name' => 'required|string|max:255',
            'item_description' => 'required|string|max:1000',
            'item_tags' => 'required|array|min:1',
            'preferred_item' => 'required|string|max:255',
            'item_category' => 'required|exists:categories,id',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $item = \App\Models\Item::with('images')->find($itemId);
            if ($item && $item->images->isNotEmpty()) {
                $rules['item_image'] = 'nullable|array';
                $rules['item_image.*'] = 'nullable|string';
            } else {
                $rules['item_image'] = 'required|array|min:1';
                $rules['item_image.*'] = 'string';
            }
        } else {
            $rules['item_image'] = 'required|array|min:1';
            $rules['item_image.*'] = 'string';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'item_image.required' => 'Please upload at least one image.',
            'item_tags.required' => 'Please select at least one tag.',
            'item_category.required' => 'Please select a category.',
        ];
    }
}
