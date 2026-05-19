<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of top-level posts (Feed).
     */
    public function index(Request $request)
    {
        $request->validate([
            'hub_type' => 'nullable|string|in:game,playlist,user',
            'hub_id' => 'nullable|integer',
        ]);

        $posts = Post::query()
            ->withFeedRelations()
            ->whereNull('parent_id')
            ->latest();

        if ($request->filled('hub_type') && $request->filled('hub_id')) {

            $posts->where(
                'hub_type',
                $request->input('hub_type')
            )
            ->where(
                'hub_id',
                $request->input('hub_id')
            );

        }

        $posts = $posts->paginate(10);

        if ($request->ajax()) {

            return response()->json([

                'html' => view(
                    'components.post.items',
                    compact('posts')
                )->render(),

                'next_page_url' => $posts
                    ->appends(
                        $request->only([
                            'hub_type',
                            'hub_id'
                        ])
                    )
                    ->nextPageUrl(),

            ]);

        }

        return view(
            'posts.index',
            compact('posts')
        );
    }

    /**
     * Store new post or reply.
     */
    public function store(Request $request)
    {
        if (auth()->user()->is_suspended) {

            abort(
                403,
                'Your account is suspended.'
            );

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

            'review_type' =>
                'nullable|string|in:recommendation,article,patch_note,announcement',

            'rating' =>
                'nullable|integer|min:1|max:10',

        ]);

        if (!empty($validated['parent_id'])) {

            $parentPost = Post::find(
                $validated['parent_id']
            );

            if (

                $parentPost &&
                $parentPost->is_locked

            ) {

                abort(
                    403,
                    'This post is locked.'
                );

            }

        }

        $post = Post::create([

            'user_id' => auth()->id(),

            'body' => $validated['body'],

            'hub_type' =>
                $validated['hub_type'] ?? null,

            'hub_id' =>
                $validated['hub_id'] ?? null,

            'parent_id' =>
                $validated['parent_id'] ?? null,

            'is_spoiler' =>
                $validated['is_spoiler']
                ?? false,

            'is_locked' =>
                $validated['is_locked']
                ?? false,

        ]);

        if (

            !empty(
                $validated['review_type']
            )

        ) {

            $post->review()->create([

                'type' =>
                    $validated['review_type'],

                'rating' =>

                    $validated['review_type']
                    === 'recommendation'

                    ? $validated['rating']

                    : null,

            ]);

        }

        if (

            !empty(
                $validated['media_ids']
            )

        ) {

            Media::whereIn(

                'id',
                $validated['media_ids']

            )->update([

                'post_id' => $post->id

            ]);

        }

        return response()->json([

            'message' =>
                'Post created successfully',

            'post' => $post

        ]);
    }

    /**
     * Single post thread.
     */
    public function show(
        Request $request,
        Post $post
    )
    {
        $post->load([

            'author',
            'media',
            'review',
            'hub'

        ]);

        if (auth()->check()) {

            $post->loadExists([

                'likes as liked_by_auth' =>
                    function ($q) {

                        $q->where(
                            'user_id',
                            auth()->id()
                        );

                    }

            ]);

        }

        if ($request->ajax()) {

            return view(
                'components.post.index',
                compact('post')
            )->render();

        }

        $replies = $post->replies()

            ->with([

                'author',
                'media'

            ])

            ->when(

                auth()->check(),

                function ($query) {

                    $query->withExists([

                        'likes as liked_by_auth' =>
                            function ($q) {

                                $q->where(
                                    'user_id',
                                    auth()->id()
                                );

                            }

                    ]);

                }

            )

            ->latest()

            ->paginate(10)

            ->withPath(
                url(
                    "/posts/{$post->id}/replies"
                )
            );

        return view(

            'posts.show',

            compact(
                'post',
                'replies'
            )

        );
    }

    /**
     * Update.
     */
    public function update(
        Request $request,
        Post $post
    )
    {
        if (

            auth()->user()->is_suspended

        ) {

            abort(
                403,
                'Account suspended.'
            );

        }

        if (

            auth()->id()
            !==
            $post->user_id

        ) {

            return response()->json([

                'message' =>
                    'Unauthorized actions.'

            ], 403);

        }

        if (

            $post->is_locked &&
            !auth()->user()->is_admin

        ) {

            abort(
                403,
                'Locked post.'
            );

        }

        $validated = $request->validate([

            'body' =>
                'required|string|max:5000',

            'media_ids' =>
                'present|array',

            'media_ids.*' =>
                'exists:media,id',

            'rating' =>
                'nullable|integer|min:1|max:10',

            'is_spoiler' =>
                'boolean',

            'is_locked' =>
                'boolean',

        ]);

        $post->update([

            'body' =>
                $validated['body'],

            'is_spoiler' =>
                $validated['is_spoiler']
                ?? false,

            'is_locked' =>
                $validated['is_locked']
                ?? false,

        ]);

        if (

            empty(
                $validated['media_ids']
            )

        ) {

            $post->media()->update([

                'post_id' => null

            ]);

        } else {

            $post->media()

                ->whereNotIn(

                    'id',

                    $validated['media_ids']

                )

                ->update([

                    'post_id' => null

                ]);

            Media::whereIn(

                'id',

                $validated['media_ids']

            )->update([

                'post_id' => $post->id

            ]);

        }

        if (

            isset(
                $validated['rating']
            ) &&

            method_exists(
                $post,
                'isReview'
            ) &&

            $post->isReview()

        ) {

            $post->review()->update([

                'rating' =>
                    $validated['rating']

            ]);

        }

        $post->load([

            'author',
            'media',
            'review',
            'hub'

        ]);

        if ($post->parent_id) {

            $html = view(

                'components.post.comment',

                [

                    'comment' => $post

                ]

            )->render();

        } else {

            $html = view(

                'components.post.index',

                compact('post')

            )->render();

        }

        return response()->json([

            'message' =>
                'Updated successfully!',

            'html' => $html

        ]);
    }

    /**
     * Delete.
     */
    public function destroy(Post $post)
    {
        if (

            auth()->user()->is_suspended

        ) {

            abort(
                403,
                'Account suspended.'
            );

        }

        if (

            auth()->id()
            !==
            $post->user_id

            &&

            !auth()->user()->is_admin

        ) {

            abort(
                403,
                'Unauthorized.'
            );

        }

        $post->delete();

        return redirect()

            ->back()

            ->with(

                'success',

                'Post deleted successfully.'

            );
    }

    /**
     * Replies pagination.
     */
    public function getReplies(
        Request $request,
        Post $post
    )
    {
        $replies = $post->replies()

            ->with([

                'author',
                'media'

            ])

            ->when(

                auth()->check(),

                function ($query) {

                    $query->withExists([

                        'likes as liked_by_auth' =>
                            function ($q) {

                                $q->where(
                                    'user_id',
                                    auth()->id()
                                );

                            }

                    ]);

                }

            )

            ->latest()

            ->paginate(10);

        return response()->json([

            'html' => view(

                'components.post.replies-items',

                compact('replies')

            )->render(),

            'next_page_url' =>

                $replies->nextPageUrl(),

        ]);
    }
}