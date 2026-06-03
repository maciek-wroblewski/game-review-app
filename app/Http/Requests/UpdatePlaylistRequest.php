<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        $playlist = $this->route('playlist');
        return $playlist->users->contains(auth()->id());
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover' => 'nullable|image|max:2048',
            'is_public' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->missing('is_public')) {
            $this->merge(['is_public' => false]);
        }
    }
}
