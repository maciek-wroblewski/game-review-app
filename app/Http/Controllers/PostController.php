<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Game;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Mail\NewPostMail;
use App\Mail\NewCommentMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Concerns\HasPaginatedResponses;

use App\Http\Controllers\Concerns\HasSidebarWidgets;

class PostController extends Controller
{
    use HasPaginatedResponses, HasSidebarWidgets;
    /**
     * Display a listing of top-level posts (Feed).
     */
    public function index(Request $request)
    {
        // Validate scoped hub filters if provided
        $request->validate([
            'hub_type' => 'nullable|string|in:game,playlist,user',
            'hub_id'   => 'nullable|integer',
            'filter'   => 'nullable|string|in:latest,trending,reviews',
        ]);

        $filter = $request->query('filter', 'latest');

        // Context filter: Is it a specific Hub? (e.g., Profile view)
        if ($request->filled('hub_type') && $request->filled('hub_id')) {
            $posts = Post::query()
                ->withFeedRelations()
                ->whereNull('parent_id')
                ->where('hub_type', $request->input('hub_type'))
                ->where('hub_id', $request->input('hub_id'))
                ->latest()
                ->simplePaginate(10);
        } elseif ($filter === 'trending') {
            $posts = Cache::remember("home_feed_trending_page_" . $request->get('page', 1), 600, function() {
                return Post::query()
                    ->whereNull('parent_id')
                    ->withFeedRelations()
                    ->withCount('replies')
                    ->orderByRaw('(likes_count + replies_count) DESC')
                    ->latest()
                    ->simplePaginate(10);
            });
        } elseif ($filter === 'reviews') {
            $posts = Cache::remember("home_feed_popular_reviews_page_" . $request->get('page', 1), 600, function() {
                return Post::query()
                    ->whereNull('parent_id')
                    ->has('review')
                    ->withFeedRelations()
                    ->whereHas('review', function ($q) {
                        $q->where('rating', '>=', 4);
                    })
                    ->latest()
                    ->simplePaginate(10);
            });
        } else {
            // Default: latest global feed
            $posts = Cache::remember("home_feed_global_page_" . $request->get('page', 1), 600, function() {
                return Post::query()
                    ->whereNull('parent_id')
                    ->withFeedRelations()
                    ->latest()
                    ->simplePaginate(10);
            });
        }

        $this->setLikedByAuthForPosts($posts);

        if ($request->ajax()) {
            return $this->ajaxFeed($posts, array_filter([
                'hub_type' => $request->input('hub_type'),
                'hub_id'   => $request->input('hub_id'),
                'filter'   => $filter !== 'latest' ? $filter : null,
            ]));
        }

        // Fetch unified sidebar widgets from cache
        $sidebarData = $this->getSidebarWidgetsData();
        $topGames = $sidebarData['top_games'];
        $activeUsers = $sidebarData['active_users'];

        if (auth()->check()) {
            $followingIds = auth()->user()->following()->pluck('users.id')->toArray();
            foreach ($activeUsers as $userItem) {
                $userItem->setAttribute('is_followed_by_auth', in_array($userItem->id, $followingIds));
            }
        }

        // Cache community stats for guest & member views
        $postsCount = Cache::remember('community_posts_count', 3600, function () {
            return Post::whereNull('parent_id')->count();
        });

        $usersCount = Cache::remember('community_users_count', 3600, function () {
            return User::count();
        });

        return view('posts.index', compact('posts', 'topGames', 'activeUsers', 'postsCount', 'usersCount'));
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

        $this->loadPostRelations($post);
        $html = $this->renderPostHtml($post);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
            'html' => $html,
        ]);
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

        // 1. Cache the user-agnostic post details
        $post = Cache::remember("post_show_model_{$post->id}", 3600, function() use ($post) {
            $post->loadCount('replies');
            $post->load('parent');
            
            $postsToLoad = new \Illuminate\Database\Eloquent\Collection([$post]);
            if ($post->parent) {
                $postsToLoad->push($post->parent);
            }

            $postsToLoad->load([
                'author' => function ($q) {
                    $q->with('avatar')->withCount(['followers', 'following', 'reviews']);
                },
                'media',
                'review',
                'hub'
            ]);

            if ($post->parent) {
                $post->parent->loadCount('replies');
            }
            return $post;
        });

        // 2. Set liked status dynamically in memory for the main post and parent
        $this->setLikedByAuthForPosts($post);

        Log::info("User " . auth()->id() . " viewed Post {$post->id}");

        if ($request->ajax()) {
            return view('components.post', compact('post'))->render();
        }

        // 3. Cache the replies paginator
        $page = $request->get('page', 1);
        $replies = Cache::remember("post_{$post->id}_replies_page_{$page}", 3600, function() use ($post) {
            return $post->replies()
                ->withRepliesFeed()
                ->simplePaginate(10)
                ->withPath(url("/posts/{$post->id}/replies"));
        });

        // 4. Set replies liked status dynamically in memory (1 query bulk)
        $this->setLikedByAuthForPosts($replies);

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
        $post->load('parent');

        $postsToLoad = new \Illuminate\Database\Eloquent\Collection([$post]);
        if ($post->parent) {
            $postsToLoad->push($post->parent);
        }

        $postsToLoad->load([
            'author' => function ($q) {
                $q->with('avatar')->withCount(['followers', 'following', 'reviews']);
            },
            'media',
            'review',
            'hub'
        ]);

        if ($post->parent) {
            $post->parent->loadCount('replies');
        }

        if (auth()->check()) {
            $postsToLoad->loadExists(['likes as liked_by_auth' => function ($q) {
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
        $hubType = $post->hub_type;
        $hubId = $post->hub_id;
        $isReview = $post->isReview();
        
        // Determine if the action is taking place on the post's dedicated show view
        $referer = request()->headers->get('referer');
        $refererPath = $referer ? parse_url($referer, PHP_URL_PATH) : '';
        $isPostShowView = rtrim($refererPath, '/') === "/posts/{$post->id}";
        
        $post->delete();

        if ($request->ajax() || $request->wantsJson()) {
            $html = '';
            if ($isReview && $hubType === 'game') {
                $html = view('components.post.create-form', [
                    'hubType' => 'game',
                    'hubId' => $hubId,
                    'reviewType' => 'recommendation'
                ])->render();
            }

            return response()->json([
                'message' => __('common.post_deleted'),
                'success' => true,
                'html'    => $html
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
            ->withRepliesFeed()
            ->simplePaginate(10);

        return $this->ajaxFeed($replies, [], 'components.post.replies-items', 'replies');
    }
}
