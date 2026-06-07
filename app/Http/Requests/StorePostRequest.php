<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()->is_suspended;
    }

    public function rules(): array
    {
        return [
            'body' => 'required|string|max:5000',
            'hub_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $hubType = $this->input('hub_type');
                    if ($hubType && $value) {
                        $tableMap = [
                            'game' => 'games',
                            'user' => 'users',
                            'playlist' => 'playlists',
                        ];
                        $table = $tableMap[$hubType] ?? null;
                        if ($table && ! DB::table($table)->where('id', $value)->exists()) {
                            $fail(__('validation.exists', ['attribute' => $attribute]));
                        }
                    }
                },
            ],
            'hub_type' => 'nullable|in:game,user,playlist',
            'parent_id' => 'nullable|exists:posts,id',
            'review_type' => 'nullable|in:recommendation',
            'rating' => 'nullable|integer|min:1|max:10',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'is_spoiler' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }
}
