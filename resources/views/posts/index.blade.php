<x-layout :headtitle="__('common.community')">
    <style>
        /* ── Community Page Premium Styling ── */
        .community-hero {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 60%, #083c8c 100%);
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(13, 110, 253, 0.25);
            margin-bottom: 2rem;
        }

        .community-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .community-hero::after {
            content: '';
            position: absolute;
            bottom: -60%;
            left: -5%;
            width: 350px;
            height: 350px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            pointer-events: none;
        }

        .community-hero-stat {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
            transition: background 0.2s ease;
        }

        .community-hero-stat:hover {
            background: rgba(255, 255, 255, 0.18);
        }

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

        .community-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            display: inline-block;
            animation: communityPulse 2s ease-in-out infinite;
        }

        @keyframes communityPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.6; transform: scale(1.35); }
        }
    </style>

    <div class="container-xl py-4">

        {{-- ── HERO BANNER ── --}}
        <section class="community-hero mb-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position: relative; z-index: 1;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="community-pulse-dot"></span>
                        <span class="text-white-50 small fw-semibold text-uppercase letter-spacing-wide">{{ __('common.community') }}</span>
                    </div>
                    <h1 class="text-white fw-bold fs-2 mb-1">{{ __('posts.global_timeline') }}</h1>
                    <p class="text-white-50 mb-0">{{ __('posts.global_timeline_description') }}</p>
                </div>

                {{-- Live stat pills --}}
                <div class="d-flex gap-2 flex-wrap">
                    <div class="community-hero-stat text-center">
                        <div class="text-white fw-bold fs-5 lh-1 mb-1">{{ $postsCount }}</div>
                        <div class="text-white-50 small">{{ __('posts.title') }}</div>
                    </div>
                    <div class="community-hero-stat text-center">
                        <div class="text-white fw-bold fs-5 lh-1 mb-1">{{ $usersCount }}</div>
                        <div class="text-white-50 small">{{ __('home.top_reviewers') }}</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── MAIN GRID LAYOUT ── --}}
        <div class="row g-4">

            {{-- ── LEFT COLUMN: FEED ── --}}
            <div class="col-lg-8">

                {{-- Quick Create Post (auth only) --}}
                @auth
                    <div class="mb-4">
                        <x-post.create-form />
                    </div>
                @endauth

                {{-- Feed Filter Tabs --}}
                <ul class="nav nav-tabs premium-tabs mb-4 gap-2 flex-nowrap overflow-auto pb-1">
                    <li class="nav-item">
                        <a class="nav-link text-nowrap d-flex align-items-center {{ !request('filter') || request('filter') === 'latest' ? 'active' : '' }}"
                           href="{{ url('/posts') }}">
                            <i class="bi bi-clock-history me-1"></i> {{ __('home.global_feed') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-nowrap d-flex align-items-center {{ request('filter') === 'trending' ? 'active' : '' }}"
                           href="{{ url('/posts?filter=trending') }}">
                            <i class="bi bi-fire me-1"></i> {{ __('home.trending_posts') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-nowrap d-flex align-items-center {{ request('filter') === 'reviews' ? 'active' : '' }}"
                           href="{{ url('/posts?filter=reviews') }}">
                            <i class="bi bi-award-fill me-1"></i> {{ __('home.popular_reviews') }}
                        </a>
                    </li>
                </ul>

                {{-- Feed Contents --}}
                <div class="feed-container">
                    <x-post.list :posts="$posts" feedId="global-posts-feed" :emptyMessage="__('posts.no_posts')" />
                </div>

            </div>

            {{-- ── RIGHT COLUMN: SIDEBAR ── --}}
            <div class="col-lg-4">

                {{-- Auth user quick profile card --}}
                @auth
                    <x-user.home-sidebar-profile />
                @endauth

                {{-- Top Rated Games --}}
                <x-game.top-games-sidebar :games="$topGames" />

                {{-- Active Reviewers --}}
                <x-user.active-users :users="$activeUsers" />

                {{-- Community CTA for guests --}}
                @guest
                    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                        <div class="card-body text-center p-4"
                             style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                            <i class="bi bi-people-fill text-white fs-1 mb-3 d-block"></i>
                            <h5 class="text-white fw-bold mb-2">{{ __('auth.join_community') }}</h5>
                            <p class="text-white-50 small mb-3">{{ __('common.login_ad') }}</p>
                            <div class="d-grid gap-2">
                                <a href="/register" class="btn btn-light fw-semibold rounded-pill">
                                    {{ __('common.register') }}
                                </a>
                                <a href="/login" class="btn btn-outline-light rounded-pill">
                                    {{ __('common.login') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endguest

            </div>
        </div>

    </div>
</x-layout>