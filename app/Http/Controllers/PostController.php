<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Mail\NewPostMail;
use App\Mail\NewCommentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of top-level posts (Feed).
     */
    public function index(Request $request)
    {
        // Validate scoped hub filters if provided
        $request->validate([
            'hub_type' => 'nullable|string|in:game,playlist,user',
            'hub_id' => 'nullable|integer',
        ]);

        $posts = Post::query()
            ->withFeedRelations() // Leveraging your built-in clean relation loader scope
            ->whereNull('parent_id')
            ->latest();

        // Context filter: Is it a specific Hub? (e.g., Playlist or Profile view)
        if ($request->filled('hub_type') && $request->filled('hub_id')) {
            $posts->where('hub_type', $request->input('hub_type'))
                ->where('hub_id', $request->input('hub_id'));
        } else {
            // Global feed filter: Only show standard feed items or fallback constraints if needed
            // optional: ->whereNull('hub_type'); // dynamic depending on if global feed shows everything
        }

        $posts = $posts->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                // Reuses your uniform post view stack
                'html' => view('components.post.items', compact('posts'))->render(),
                'next_page_url' => $posts->appends($request->only(['hub_type', 'hub_id']))->nextPageUrl(),
            ]);
        }

        return view('posts.index', compact('posts'));
    }

    /**
     * Store a newly created post or reply.
     */
    public function store(Request $request)
    {
        if (auth()->user()->is_suspended) {
            abort(403, 'Your account is suspended.');
        }
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

        $currentUser = auth()->user();

        if (!empty($validated['parent_id'])) {
            $parentPost = Post::with('author')->find($validated['parent_id']);
            Log::info("User {$currentUser->id} commented on Post {$parentPost->id} (New Comment ID: {$post->id})");
            
            if ($parentPost && ($parentPost->is_locked || $parentPost->admin_locked)) {
                return response()->json(['message' => 'This post is locked.'], 403);
            }

            if ($parentPost && $parentPost->user_id && $parentPost->user_id !== $currentUser->id) {
                // 1. Zapis do bazy z linkiem (target_url)
                Notification::create([
                    'user_id' => $parentPost->user_id,
                    'from_user_id' => $currentUser->id,
                    'type' => 'comment',
                    'message' => __(':username commented on your post.', ['username' => $currentUser->username]),
                    'target_url' => url('/posts/' . $parentPost->id),
                ]);

                if ($parentPost->author && $parentPost->author->email) {
                    Mail::to($parentPost->author->email)->queue(new NewCommentMail($currentUser, $post, $parentPost));
                }
            }
        } 
        else {
            $followers = $currentUser->followers;

            foreach ($followers as $follower) {
                Notification::create([
                    'user_id' => $follower->id,
                    'from_user_id' => $currentUser->id,
                    'type' => 'new_post',
                    'message' => __(':username just posted a new post.', ['username' => $currentUser->username]),
                    'target_url' => url('/posts/' . $post->id), // Link do nowego posta
                ]);

                Mail::to($follower->email)->queue(new \App\Mail\NewPostMail($currentUser, $post));
            }
        }

        Log::info("User {$currentUser->id} created Post {$post->id}");

        if (! empty($validated['review_type'])) {
            $post->review()->create([
                'type' => $validated['review_type'],
                'rating' => $validated['review_type'] === 'recommendation' ? $validated['rating'] : null,
            ]);
            Log::info("User {$currentUser->id} created Review for Post {$post->id}");
        }

        if (! empty($validated['media_ids'])) {
            Media::whereIn('id', $validated['media_ids'])->update(['post_id' => $post->id]);
            Log::info("User {$currentUser->id} attached Media to Post {$post->id}");
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }

    /**
     * Display a Single Post Thread page.
     */
    public function show(Request $request, Post $post)
    {
        // 1. Load data for the main post card
        $post->load(['author', 'media', 'review', 'hub'])->loadCount('replies');
        Log::info("User " . auth()->id() . " viewed Post {$post->id}");
        if (auth()->check()) {
            $post->loadExists(['likes as liked_by_auth' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        // If the front-end requests a single post wrapper via AJAX (e.g. to reset/cancel edit views)
        if ($request->ajax()) {
            return view('components.post', compact('post'))->render();
        }

        // 2. Fetch the initial block of replies for the thread
        $replies = $post->replies()
            ->with(['author', 'media'])
            ->withCount('replies')
            ->when(auth()->check(), function ($query) {
                $query->withExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->latest()
            ->orderByDesc('is_pinned')
            ->paginate(10)
            ->withPath(url("/posts/{$post->id}/replies"));

        return view('posts.show', compact('post', 'replies'));
    }

    /**
     * Update the specified post or comment inline via AJAX.
     */
    public function update(Request $request, Post $post)
    {
        if (auth()->user()->is_suspended) {
            abort(403, 'Your account is suspended.');
        }
        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized actions.'], 403);
        }
        if ($post->admin_locked && !auth()->user()->is_admin) {
            return response()->json(['message' => 'This post is locked by an administrator.'], 403);
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
            $html = view('components.post', compact('post'))->render();
        }

        return response()->json([
            'message' => 'Updated successfully!',
            'html' => $html,
        ], 200);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Post $post)
    {
        if (auth()->user()->is_suspended) {
            abort(403, 'Your account is suspended.');
        }
        if (auth()->id() !== $post->user_id &&
            !auth()->user()->is_admin
            ) {
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
            ->paginate(10); // Changed from cursorPaginate to standard pagination

        return response()->json([
            // FIX: Render replies-items HERE, not replies-list wrapper layout!
            'html' => view('components.post.replies-items', compact('replies'))->render(),
            'next_page_url' => $replies->nextPageUrl(),
        ]);
    }
}
