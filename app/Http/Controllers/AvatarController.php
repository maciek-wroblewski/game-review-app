<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class AvatarController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048',
        ]);

        $file = $request->file('file');

        $path = $file->store('avatars', 'public');

        $media = Media::create([
            'file_path' => 'http://127.0.0.1:8000/storage/' . $path,
            'mime_type' => $file->getClientMimeType(),
        ]);

        $user = auth()->user();

        $user->avatar_media_id = $media->id;

        $user->save();

        return back();
    }
}