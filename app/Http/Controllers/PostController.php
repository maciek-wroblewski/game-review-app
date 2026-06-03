<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
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
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $currentUser = auth()->user();

        // Check lock status BEFORE creating the reply
        if (!empty($validated['parent_id'])) {
            $parentPost = Post::with('author')->find($validated['parent_id']);

            if (!$parentPost) {
                abort(404, 'Parent post not found.');
            }

            if ($parentPost->is_locked || $parentPost->admin_locked) {
                return response()->json(['message' => 'This post is locked.'], 403);
            }
        }

        $post = Post::create([
            'user_id' => $currentUser->id,
            'body' => $validated['body'],
            'hub_type' => $validated['hub_type'],
            'hub_id' => $validated['hub_id'],
            'parent_id' => $validated['parent_id'],
            'is_spoiler' => $validated['is_spoiler'] ?? false,
            'is_locked' => $validated['is_locked'] ?? false,
        ]);

        Log::info("User {$currentUser->id} created Post {$post->id}");

        $this->handleNotifications($post, $currentUser, $parentPost ?? null);
        $this->attachMedia($post, $validated);

        if (!empty($validated['review_type'])) {
            $post->review()->create([
                'type' => $validated['review_type'],
                'rating' => $validated['review_type'] === 'recommendation' ? $validated['rating'] : null,
            ]);
            Log::info("User {$currentUser->id} created Review for Post {$post->id}");
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }

    /**
     * Handle notifications and emails for post creation.
     */
    private function handleNotifications(Post $post, $currentUser, ?Post $parentPost): void
    {
        if ($parentPost) {
            // Reply notification
            Log::info("User {$currentUser->id} commented on Post {$parentPost->id} (New Comment ID: {$post->id})");

            if ($parentPost->user_id && $parentPost->user_id !== $currentUser->id) {
                Notification::create([
                    'user_id' => $parentPost->user_id,
                    'from_user_id' => $currentUser->id,
                    'type' => 'comment',
                    'message' => __(':username commented on your post.', ['username' => $currentUser->username]),
                    'target_url' => url('/posts/' . $parentPost->id),
                    'post_id' => $post->id,
                ]);

                if ($parentPost->author && $parentPost->author->email) {
                    Mail::to($parentPost->author->email)->queue(new NewCommentMail($currentUser, $post, $parentPost));
                }
            }
        } else {
            // New post notification to followers
            $currentUser->load('followers');

            foreach ($currentUser->followers as $follower) {
                Notification::create([
                    'user_id' => $follower->id,
                    'from_user_id' => $currentUser->id,
                    'type' => 'new_post',
                    'message' => __(':username just posted a new post.', ['username' => $currentUser->username]),
                    'target_url' => url('/posts/' . $post->id),
                    'post_id' => $post->id,
                ]);

                Mail::to($follower->email)->queue(new NewPostMail($currentUser, $post));
            }
        }
    }

    /**
     * Attach media to post if provided.
     */
    private function attachMedia(Post $post, array $validated): void
    {
        if (!empty($validated['media_ids'])) {
            Media::whereIn('id', $validated['media_ids'])->update(['post_id' => $post->id]);
            $userId = $validated['user_id'] ?? auth()->id();
            Log::info("User {$userId} attached Media to Post {$post->id}");
        }
    }

    /**
     * Display a Single Post Thread page.
     */
    public function show(Request $request, Post $post)
    {
        if ($post->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_post_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('posts.deleted');
        }

        $post->loadCount('replies');
        $this->loadPostRelations($post);
        Log::info("User " . auth()->id() . " viewed Post {$post->id}");

        if ($request->ajax()) {
            return view('components.post', compact('post'))->render();
        }

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
    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();

        $post->update([
            'body' => $validated['body'],
            'is_spoiler' => $validated['is_spoiler'] ?? false,
            'is_locked' => $validated['is_locked'] ?? false,
        ]);

        $this->syncMedia($post, $validated);

        if (isset($validated['rating']) && method_exists($post, 'isReview') && $post->isReview()) {
            $post->review()->update(['rating' => $validated['rating']]);
        }

        $this->loadPostRelations($post);

        $html = $this->renderPostHtml($post);
        Log::info("User " . auth()->id() . " updated Post {$post->id}");
        return response()->json([
            'message' => 'Updated successfully!',
            'html' => $html,
        ], 200);
    }

    /**
     * Sync media attachments with the post.
     */
    private function syncMedia(Post $post, array $validated): void
    {
        if (empty($validated['media_ids'])) {
            $post->media()->update(['post_id' => null]);
        } else {
            $post->media()->whereNotIn('id', $validated['media_ids'])->update(['post_id' => null]);
            Media::whereIn('id', $validated['media_ids'])->update(['post_id' => $post->id]);
        }
    }

    /**
     * Load standard post relations and like status.
     */
    private function loadPostRelations(Post $post): void
    {
        $post->load(['author', 'media', 'review', 'hub']);
        if (auth()->check()) {
            $post->loadExists(['likes as liked_by_auth' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }
    }

    /**
     * Render the appropriate HTML for a post (comment or root post).
     */
    private function renderPostHtml(Post $post): string
    {
        if ($post->parent_id) {
            return view('components.post.comment', compact('post'))->render();
        }

        return view('components.post', compact('post'))->render();
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);
        $parentId = $post->parent_id;
        
        // Determine if the action is taking place on the post's dedicated show view
        $referer = request()->headers->get('referer');
        $refererPath = $referer ? parse_url($referer, PHP_URL_PATH) : '';
        $isPostShowView = rtrim($refererPath, '/') === "/posts/{$post->id}";
        
        $post->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => __('common.post_deleted'),
                'success' => true,
                'html'    => '' // Allows frontend to cleanly empty the DOM element if handled like update/create
            ]);
        }

        // If we are on the post's own view, we must redirect away so the user doesn't hit a 404 page
        if ($isPostShowView) {
            $redirectUrl = $parentId ? url('/posts/' . $parentId) : url('/');
            return redirect($redirectUrl)->with('success', __('common.post_deleted'));
        }

        // Otherwise, simply re-render the view they are currently on (Feed, Profile, etc.)
        return back()->with('success', __('common.post_deleted'));
    }

    /**
     * Asynchronously fetch additional reply segments (Infinite Comment Scrolling).
     */
    public function getReplies(Request $request, Post $post)
    {
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
            ->paginate(10);

        return response()->json([
            'html' => view('components.post.replies-items', compact('replies'))->render(),
            'next_page_url' => $replies->nextPageUrl(),
        ]);
    }
}
