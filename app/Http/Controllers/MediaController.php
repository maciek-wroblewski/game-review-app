<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,png,gif|max:2048',
            'post_id' => 'nullable|exists:posts,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        $media = Media::create([
            'post_id' => $request->input('post_id'),
            'file_path' => Storage::url($path), // Saves /storage/uploads/filename.jpg for easy HTML use
            'mime_type' => $file->getClientMimeType(), // Fixed function call
        ]);

        return response()->json([
            'message' => 'File uploaded successfully!',
            'media' => $media, // Returns the full record to use in HTML
        ]);
    }
    
    public function show($media_id)
    {
        $media = Media::find($media_id, 'id');
        return $media->filepath;
    }
}