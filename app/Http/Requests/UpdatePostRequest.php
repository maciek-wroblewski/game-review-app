<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');
        if ($this->user()->is_suspended) {
            return false;
        }
        if ($this->user()->is_admin) {
            return true;
        }
        if ($this->user()->id !== $post->user_id) {
            return false;
        }
        if ($post->admin_locked && ! $this->user()->is_admin) {
            return false;
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => 'required|string|max:5000',
            'media_ids' => 'present|array',
            'media_ids.*' => 'exists:media,id',
            'rating' => 'nullable|integer|min:1|max:10',
            'is_spoiler' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }
}
