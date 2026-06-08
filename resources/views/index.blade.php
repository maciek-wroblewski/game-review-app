<x-layout headtitle="Home">
    <style>
        /* Modern Glassmorphic Dashboard & Tabs Styling */
        .premium-dashboard-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .premium-dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        .premium-tabs {
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .premium-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            position: relative;
            transition: color 0.2s ease;
        }

        .premium-tabs .nav-link:hover {
            color: #0d6efd;
            background: transparent;
        }

        .premium-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border: none;
        }

        .premium-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #0d6efd;
            border-radius: 2px;
            box-shadow: 0 2px 10px rgba(13, 110, 253, 0.4);
        }

        .playlist-badge {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
    </style>

    <div class="container-xl py-4">
        <!-- 1. HERO CAROUSEL: Top Trending Games -->
        <section class="mb-5">
            <x-game.trending :games="$trendingGames" />
        </section>

        <!-- 2. MAIN GRID LAYOUT -->
        <div class="row g-4">
            
            <!-- LEFT COLUMN: FEEDS & TIMELINES -->
            <div class="col-lg-8">
                @auth
                    <!-- Quick Create Post Widget -->
                    <div class="mb-4">
                        <x-post.create-form />
                    </div>
                @endauth

                <!-- Feed Navigation Tabs -->
                <ul class="nav nav-tabs premium-tabs mb-4 gap-2 flex-nowrap overflow-auto pb-1">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link text-nowrap d-flex align-items-center {{ $tab === 'my_feed' ? 'active' : '' }}" href="{{ url('/?tab=my_feed') }}">
                                <i class="bi bi-people-fill me-1"></i> {{ __('home.my_feed') }}
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link text-nowrap d-flex align-items-center {{ $tab === 'global_feed' ? 'active' : '' }}" href="{{ url('/?tab=global_feed') }}">
                            <i class="bi bi-globe me-1"></i> {{ __('home.global_feed') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-nowrap d-flex align-items-center {{ $tab === 'trending' ? 'active' : '' }}" href="{{ url('/?tab=trending') }}">
                            <i class="bi bi-fire me-1"></i> {{ __('home.trending_posts') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-nowrap d-flex align-items-center {{ $tab === 'popular_reviews' ? 'active' : '' }}" href="{{ url('/?tab=popular_reviews') }}">
                            <i class="bi bi-award-fill me-1"></i> {{ __('home.popular_reviews') }}
                        </a>
                    </li>
                </ul>

                <!-- Feed Contents -->
                <div class="feed-container">
                    @php
                        $emptyMsg = __('posts.no_posts');
                        if ($tab === 'my_feed') {
                            $emptyMsg = __('home.no_feed_posts');
                        } elseif ($tab === 'popular_reviews') {
                            $emptyMsg = __('home.no_reviews');
                        }
                    @endphp

                    <x-post.list :posts="$posts" feedId="home-posts-feed" :emptyMessage="$emptyMsg" />
                </div>
            </div>

            <!-- RIGHT COLUMN: SIDEBAR WIDGETS -->
            <div class="col-lg-4">
                
                <!-- Authenticated User Profile Summary -->
                @auth
                    <x-user.home-sidebar-profile />
                    <x-playlist.home-sidebar-playlists :playlists="$playlists" />
                @endauth

                <!-- 3. SIDEBAR COMPONENT: Top Rated Games -->
                <x-game.top-games-sidebar :games="$topGames" />

                <!-- 4. SIDEBAR COMPONENT: Active Reviewers -->
                <x-user.active-users :users="$activeUsers" />
                
            </div>
        </div>
    </div>
</x-layout>