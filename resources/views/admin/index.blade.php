<x-layout headtitle="Admin Panel">

    <div class="container py-5">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">

            <div>

                <h1 class="fw-bold mb-1">

                    <i class="bi bi-shield-lock-fill text-danger me-2"></i>

                    Admin Panel

                </h1>

                <p class="text-muted mb-0">

                    Moderation dashboard and platform analytics

                </p>

            </div>

            <span class="badge bg-danger fs-6 px-3 py-2">

                ADMIN ACCESS

            </span>

        </div>

        <!-- Stats -->
        <div class="row g-4 mb-5">

            <!-- Users -->
            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-4">

                        <i class="bi bi-people-fill text-primary fs-1"></i>

                        <h2 class="fw-bold mt-3">

                            {{ $userCount }}

                        </h2>

                        <p class="text-muted mb-0">
                            Users
                        </p>

                    </div>

                </div>

            </div>

            <!-- Posts -->
            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-4">

                        <i class="bi bi-chat-left-text-fill text-success fs-1"></i>

                        <h2 class="fw-bold mt-3">

                            {{ $postCount }}

                        </h2>

                        <p class="text-muted mb-0">
                            Posts
                        </p>

                    </div>

                </div>

            </div>

            <!-- Reviews -->
            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-4">

                        <i class="bi bi-star-fill text-warning fs-1"></i>

                        <h2 class="fw-bold mt-3">

                            {{ $reviewCount }}

                        </h2>

                        <p class="text-muted mb-0">
                            Reviews
                        </p>

                    </div>

                </div>

            </div>

            <!-- Notifications -->
            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-4">

                        <i class="bi bi-bell-fill text-danger fs-1"></i>

                        <h2 class="fw-bold mt-3">

                            {{ $notificationCount }}

                        </h2>

                        <p class="text-muted mb-0">
                            Notifications
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <div class="row g-4">

            <!-- Latest Users -->
            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white border-0 py-3">

                        <h4 class="fw-bold mb-0">

                            <i class="bi bi-person-plus-fill text-primary me-2"></i>

                            Latest Users

                        </h4>

                    </div>

                    <div class="card-body">

                        @forelse($latestUsers as $user)

                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                                <div class="d-flex align-items-center gap-3">

                                    <x-user.avatar
                                        :user="$user"
                                        layout="compact"
                                        size="48px" />

                                    <div>

                                        <div class="fw-semibold">

                                            {{ $user->username }}

                                        </div>

                                        <small class="text-muted">

                                            Joined {{ $user->created_at->diffForHumans() }}

                                        </small>

                                    </div>

                                </div>

                                <a href="/users/{{ $user->username }}"
                                   class="btn btn-sm btn-outline-primary">

                                    View

                                </a>

                            </div>

                        @empty

                            <p class="text-muted mb-0">

                                No users found.

                            </p>

                        @endforelse

                    </div>

                </div>

            </div>

            <!-- Latest Posts -->
            <div class="col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white border-0 py-3">

                        <h4 class="fw-bold mb-0">

                            <i class="bi bi-chat-left-fill text-success me-2"></i>

                            Latest Posts

                        </h4>

                    </div>

                    <div class="card-body">

                        @forelse($latestPosts as $post)

                            <div class="py-3 border-bottom">

                                <div class="d-flex justify-content-between align-items-start mb-2">

                                    <div>

                                        <div class="fw-semibold">

                                            {{ $post->user->username ?? 'Deleted User' }}

                                        </div>

                                        <small class="text-muted">

                                            {{ $post->created_at->diffForHumans() }}

                                        </small>

                                    </div>

                                    <form method="POST"
                                          action="/posts/{{ $post->id }}">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">

                                            <i class="bi bi-trash-fill"></i>

                                        </button>

                                    </form>

                                </div>

                                <div class="text-muted small">

                                    {{ Str::limit($post->content, 120) }}

                                </div>

                            </div>

                        @empty

                            <p class="text-muted mb-0">

                                No posts found.

                            </p>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>