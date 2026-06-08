<?php

namespace App\Http\Controllers\Concerns;

trait HasPaginatedResponses
{
    /**
     * Handles card grid pagination (games, users, playlists, etc.) with pre-rendered views.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator  $paginator
     * @param  string  $viewName
     * @param  string  $dataKey
     * @param  array  $extraData
     * @param  bool  $wrapInGrid
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxCardGrid($paginator, string $viewName, string $dataKey, array $extraData = [], bool $wrapInGrid = true)
    {
        $html = '';
        
        foreach ($paginator as $item) {
            if (str_starts_with($viewName, 'components.')) {
                $componentName = str_replace('components.', '', $viewName);
                
                // Build dynamic attributes string for variables in extraData + primary dataKey
                $attributesString = '';
                foreach (array_keys($extraData) as $key) {
                    $attributesString .= " :$key=\"\$$key\"";
                }
                $attributesString .= " :$dataKey=\"\$$dataKey\"";

                $content = \Illuminate\Support\Facades\Blade::render(
                    "<x-dynamic-component :component=\"\$component\"$attributesString />",
                    array_merge(['component' => $componentName, $dataKey => $item], $extraData)
                );
            } else {
                $content = view($viewName, array_merge([$dataKey => $item], $extraData))->render();
            }
            
            if ($wrapInGrid) {
                $html .= '<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">' . $content . '</div>';
            } else {
                $html .= $content;
            }
        }

        return response()->json([
            'html' => $html,
            'next_page_url' => $paginator->nextPageUrl(),
        ]);
    }

    /**
     * Handles standard feed list pagination.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator  $paginator
     * @param  array  $appends
     * @param  string  $viewName
     * @param  string  $dataKey
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxFeed($paginator, array $appends = [], string $viewName = 'components.post.items', string $dataKey = 'posts')
    {
        $paginatorData = $paginator;
        if (!empty($appends)) {
            $paginatorData = $paginator->appends($appends);
        }

        return response()->json([
            'html' => view($viewName, [$dataKey => $paginatorData])->render(),
            'next_page_url' => $paginatorData->nextPageUrl(),
        ]);
    }

    /**
     * Set liked_by_auth attribute for posts in bulk to avoid subquery overhead and allow caching.
     */
    protected function setLikedByAuthForPosts($posts)
    {
        $isSingle = $posts instanceof \App\Models\Post;
        $postsCollection = $isSingle ? collect([$posts]) : $posts;

        if (auth()->check()) {
            $postIds = collect();
            foreach ($postsCollection as $post) {
                $postIds->push($post->id);
                if ($post->relationLoaded('parent') && $post->parent) {
                    $postIds->push($post->parent->id);
                }
            }
            
            $likedPostIds = \Illuminate\Support\Facades\DB::table('likes')
                ->where('user_id', auth()->id())
                ->where('likeable_type', (new \App\Models\Post)->getMorphClass())
                ->whereIn('likeable_id', $postIds->filter()->unique())
                ->pluck('likeable_id')
                ->toArray();

            foreach ($postsCollection as $post) {
                $post->setAttribute('liked_by_auth', in_array($post->id, $likedPostIds));
                if ($post->relationLoaded('parent') && $post->parent) {
                    $post->parent->setAttribute('liked_by_auth', in_array($post->parent->id, $likedPostIds));
                }
            }
        } else {
            foreach ($postsCollection as $post) {
                $post->setAttribute('liked_by_auth', false);
                if ($post->relationLoaded('parent') && $post->parent) {
                    $post->parent->setAttribute('liked_by_auth', false);
                }
            }
        }
        return $posts;
    }
}
