<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Concerns\HasPaginatedResponses;

use App\Http\Controllers\Concerns\HasSidebarWidgets;

class HomeController extends Controller
{
    use HasPaginatedResponses, HasSidebarWidgets;

    /**
     * Display the home page with tabs and sidebar widgets.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', auth()->check() ? 'my_feed' : 'global_feed');
        
        $allowedTabs = ['my_feed', 'global_feed', 'trending', 'popular_reviews'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = auth()->check() ? 'my_feed' : 'global_feed';
        }

        // Fetch posts depending on current tab
        if ($tab === 'my_feed' && auth()->check()) {
            $followingIds = Cache::remember("user_" . auth()->id() . "_following_ids", 3600, function() {
                return auth()->user()->following()->pluck('users.id');
            });
            if ($followingIds->isNotEmpty()) {
                $posts = Post::query()
                    ->whereNull('parent_id')
                    ->whereIn('user_id', $followingIds)
                    ->withFeedRelations()
                    ->latest()
                    ->simplePaginate(10);
            } else {
                // Return an empty paginator if they follow no one
                $posts = new \Illuminate\Pagination\Paginator(collect(), 10);
            }
        } elseif ($tab === 'trending') {
            $posts = Cache::remember("home_feed_trending_page_" . $request->get('page', 1), 600, function() {
                return Post::query()
                    ->whereNull('parent_id')
                    ->withFeedRelations()
                    ->withCount('replies')
                    ->orderByRaw('(likes_count + replies_count) DESC')
                    ->latest()
                    ->simplePaginate(10);
            });
        } elseif ($tab === 'popular_reviews') {
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
            // Global feed
            $posts = Cache::remember("home_feed_global_page_" . $request->get('page', 1), 600, function() {
                return Post::query()
                    ->whereNull('parent_id')
                    ->withFeedRelations()
                    ->latest()
                    ->simplePaginate(10);
            });
        }

        // Set liked by auth dynamically to enable caching of the queries
        $this->setLikedByAuthForPosts($posts);

        if ($request->ajax()) {
            return $this->ajaxFeed($posts, ['tab' => $tab]);
        }

        // Fetch unified sidebar widgets from cache
        $sidebarData = $this->getSidebarWidgetsData();
        $topGames = $sidebarData['top_games'];
        $activeUsers = $sidebarData['active_users'];
        $trendingGames = $sidebarData['trending_games'];

        // Calculate following status dynamically outside the cache
        if (auth()->check()) {
            $followingIds = auth()->user()->following()->pluck('users.id')->toArray();
            foreach ($activeUsers as $userItem) {
                $userItem->setAttribute('is_followed_by_auth', in_array($userItem->id, $followingIds));
            }
        }

        // For authenticated users, load their playlists
        $playlists = auth()->check()
            ? auth()->user()->playlists()->withCount('games')->where('is_system', true)->get()
            : collect();

        return view('index', compact('posts', 'topGames', 'activeUsers', 'playlists', 'tab', 'trendingGames'));
    }
}
