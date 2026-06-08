<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Game::class);
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'publisher'    => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'details'      => 'nullable|string',
            'banner_img'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_img'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo'         => 'nullable|image|mimes:jpeg,png,webp|max:1024',
            'genres'       => 'nullable|array',
            'credits'      => 'nullable|array',
        ];
    }
}
