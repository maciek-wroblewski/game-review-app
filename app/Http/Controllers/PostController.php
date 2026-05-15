<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource (Timeline).
     */
    public function index()
    {
        // Eager load relationships to prevent N+1 query problems
        $posts = Post::with(['author', 'media', 'hub', 'review'])
            ->latest() // Order by created_at DESC
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    /**
     * Display the specified resource (Single Post Page).
     */
    public function show(Post $post)
    {
        $post->load(['author', 'media', 'hub', 'review']);
        
        return view('posts.show', compact('post'));
    }

    /**
     * Update the specified resource in storage (AJAX Request).
     */
    public function update(Request $request, Post $post)
    {
        // 1. Authorization: Ensure the user actually owns this post
        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized actions.'], 403);
        }

        // 2. Validation
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'media_ids' => 'present|array', // Allow empty arrays if all media is removed
            'media_ids.*' => 'exists:media,id',
            'rating' => 'nullable|integer|min:1|max:10', // For reviews
            'is_spoiler' => 'boolean',
            'is_locked' => 'boolean',
        ]);

        // 3. Update Text Body and Toggle States
        $post->update([
            'body' => $validated['body'],
            'is_spoiler' => $validated['is_spoiler'] ?? false,
            'is_locked' => $validated['is_locked'] ?? false,
        ]);

        // 4. Update Media Attachments (Since Post hasMany Media)
        // First, dissociate any media that is no longer in the media_ids array
        if (empty($validated['media_ids'])) {
            $post->media()->update(['post_id' => null]); // Or delete() if you want to trash the files
        } else {
            // Dissociate removed media
            $post->media()->whereNotIn('id', $validated['media_ids'])->update(['post_id' => null]);
            
            // Associate any newly uploaded media
            Media::whereIn('id', $validated['media_ids'],null, null)->update(['post_id' => $post->id]);
        }

        // 5. Update Review Rating (If applicable)
        if (isset($validated['rating']) && method_exists($post, 'isReview') && $post->isReview()) {
            if ($post->review) {
                $post->review->update(['rating' => $validated['rating']]);
            }
        }

        // Return a JSON success response for our fetch() script
        return response()->json([
            'message' => 'Post updated successfully!',
            'post' => $post->fresh(['media', 'review']) // Send back fresh data just in case
        ], 200);
    }

    /**
     * Remove the specified resource from storage (AJAX Request).
     */
    public function destroy(Post $post)
    {
        // Authorization check
        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // If you are using SoftDeletes on your Post model, this will trash it.
        // If not, it permanently deletes it.
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully.'
        ], 200);
    }
}