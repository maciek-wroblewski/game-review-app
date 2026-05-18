<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['author', 'media', 'hub', 'review'])
            ->withCount('replies')
            ->whereNull('parent_id') // Top-level posts only
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return view('components.post.items', compact('posts'))->render();
        }

        return view('posts.index', compact('posts'));
    }

    public function store(Request $request)
    {
        // 1. Validate the incoming payload
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'hub_type' => 'nullable|string',
            'hub_id' => 'nullable|integer',
            'parent_id' => 'nullable|exists:posts,id',
            'is_spoiler' => 'boolean',
            'is_locked' => 'boolean',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'review_type' => 'nullable|string|in:recommendation,article,patch_note,announcement',
            'rating' => 'nullable|integer|min:1|max:10',
        ]);

        // 2. Create the Post
        $post = Post::create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'hub_type' => $validated['hub_type'],
            'hub_id' => $validated['hub_id'],
            'parent_id' => $validated['parent_id'],
            'is_spoiler' => $validated['is_spoiler'] ?? false,
            'is_locked' => $validated['is_locked'] ?? false,
        ]);

        // 3. Handle Review creation (if applicable)
        if (!empty($validated['review_type'])) {
            $post->review()->create([
                'type' => $validated['review_type'],
                'rating' => $validated['review_type'] === 'recommendation' ? $validated['rating'] : null,
            ]);
        }

        // 4. Sync Media (assuming you are using Spatie MediaLibrary or a custom media pivot table)
        if (!empty($validated['media_ids'])) {
            \App\Models\Media::whereIn('id', $validated['media_ids'], null, null)->update(['post_id' => $post->id]);
        }
        // The JS expects a 200 OK response to reload the page
        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }
    /**
     * Display the specified resource (Single Post Page).
     */

    public function show(Post $post)
    {
        // Eager-load commonly accessed relations on the single post page
        $post->load(['author', 'media', 'review', 'hub', 'likes']);

        // Eager load author & media for replies to avoid N+1 when rendering
        $replies = $post->replies()->with(['author', 'media'])->latest()->paginate(10);

        return view('posts.show', compact('post', 'replies'));
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

        if (empty($validated['media_ids'])) {
            $post->media()->update(['post_id' => null]); // Or delete() if you want to trash the files
        } else {
            // Dissociate removed media
            $post->media()->whereNotIn('id', $validated['media_ids'])->update(['post_id' => null]);

            // Associate any newly uploaded media
            Media::whereIn('id', $validated['media_ids'], null, null)->update(['post_id' => $post->id]);
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
            abort(403, 'Unauthorized.');
        }

        // Delete the post
        $post->delete($post->id);

        // Redirect back to the previous page (e.g., the post feed or single post view)
        // You can also use redirect()->route('posts.index') if you want to go to a specific page.
        return redirect()->back()->with('success', 'Post deleted successfully.');
    }


    public function getReplies(Request $request, Post $post)
    {
        // Eager load author & media to prevent N+1
        $replies = $post->replies()
            ->with(['author', 'media'])
            ->latest()
            ->paginate(1);

        // Return everything as a clean API JSON packet
            
        return response()->json([
            'html' => view('components.post.replies-list', compact('replies'))->render(),
            'next_page_url' => $replies->nextPageUrl(),
        ]);
    }
}
