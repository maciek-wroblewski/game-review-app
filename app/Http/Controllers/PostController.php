<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of top-level posts (Feed).
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with(['author', 'media', 'hub', 'review'])
            ->withCount('replies')
            ->whereNull('parent_id')
            ->when(auth()->check(), function ($query) {
                $query->withExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->latest()
            ->cursorPaginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(),
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        return view('posts.index', compact('posts'));
    }

    /**
     * Store a newly created post or reply.
     */
    public function store(Request $request)
    {
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

        $post = Post::create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'hub_type' => $validated['hub_type'],
            'hub_id' => $validated['hub_id'],
            'parent_id' => $validated['parent_id'],
            'is_spoiler' => $validated['is_spoiler'] ?? false,
            'is_locked' => $validated['is_locked'] ?? false,
        ]);

        if (!empty($validated['review_type'])) {
            $post->review()->create([
                'type' => $validated['review_type'],
                'rating' => $validated['review_type'] === 'recommendation' ? $validated['rating'] : null,
            ]);
        }

        if (!empty($validated['media_ids'])) {
            Media::whereIn('id', $validated['media_ids'])->update(['post_id' => $post->id]);
        }

        // Return a JSON response. If it's a comment, you could optionally return HTML here too!
        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }

    /**
     * Display a Single Post Thread page.
     */
    public function show(Request $request, Post $post)
    {
        // 1. Load data for the main post card
        $post->load(['author', 'media', 'review', 'hub']);
        if (auth()->check()) {
            $post->loadExists(['likes as liked_by_auth' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        // If the front-end requests a single post wrapper via AJAX (e.g. to reset/cancel edit views)
        if ($request->ajax()) {
            return view('components.post.index', compact('post'))->render();
        }

        // 2. Fetch the initial block of replies for the thread
        $replies = $post->replies()
            ->with(['author', 'media'])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->latest()
            ->paginate(10); // Changed from 1 to 10 for standard production behavior

        return view('posts.show', compact('post', 'replies'));
    }

    /**
     * Update the specified post or comment inline via AJAX.
     */
    public function update(Request $request, Post $post)
    {
        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized actions.'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'media_ids' => 'present|array',
            'media_ids.*' => 'exists:media,id',
            'rating' => 'nullable|integer|min:1|max:10',
            'is_spoiler' => 'boolean',
            'is_locked' => 'boolean',
        ]);

        $post->update([
            'body' => $validated['body'],
            'is_spoiler' => $validated['is_spoiler'] ?? false,
            'is_locked' => $validated['is_locked'] ?? false,
        ]);

        // Sync media layout
        if (empty($validated['media_ids'])) {
            $post->media()->update(['post_id' => null]);
        } else {
            $post->media()->whereNotIn('id', $validated['media_ids'])->update(['post_id' => null]);
            Media::whereIn('id', $validated['media_ids'])->update(['post_id' => $post->id]);
        }

        if (isset($validated['rating']) && method_exists($post, 'isReview') && $post->isReview()) {
            $post->review()->update(['rating' => $validated['rating']]);
        }

        // Refresh all relationship logic for clean compilation
        $post->load(['author', 'media', 'review', 'hub']);
        if (auth()->check()) {
            $post->loadExists(['likes as liked_by_auth' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        /**
         * THE MULTI-VIEW WORKFLOW:
         * If this entry has a parent_id, it's a comment! Send back comment HTML.
         * If it does NOT have a parent_id, it's a root post! Send back the full card HTML.
         */
        if ($post->parent_id) {
            $html = view('components.post.comment', ['comment' => $post])->render();
        } else {
            $html = view('components.post.index', compact('post'))->render();
        }

        return response()->json([
            'message' => 'Updated successfully!',
            'html' => $html
        ], 200);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Post $post)
    {
        if (auth()->id() !== $post->user_id) {
            abort(403, 'Unauthorized.');
        }

        $post->delete();

        return redirect()->back()->with('success', 'Post deleted successfully.');
    }

    /**
     * Asynchronously fetch additional reply segments (Infinite Comment Scrolling).
     */
    public function getReplies(Request $request, Post $post)
    {
        $replies = $post->replies()
            ->with(['author', 'media'])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->latest()
            ->cursorPaginate(10); // <-- Switch here too for comment infinite scrolls

        return response()->json([
            'html' => view('components.post.replies-list', compact('replies'))->render(),
            'next_page_url' => $replies->nextPageUrl(),
        ]);
    }
}
