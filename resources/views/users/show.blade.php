<x-layout headtitle="{{ $user->username }}">
    <div class="container py-5">
    <!-- Profile Header -->
    <x-user.card :user="$user" />
    
    <!-- Grid Row instead of d-flex -->
    <div class="row g-4">
        
        <!-- Recent Reviews (Left Column) -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h2 class="fw-bold mb-1">
                                Recent Posts
                            </h2>
                            <p class="text-muted mb-0">
                                Latest thoughts and opinions from {{ $user->username }}
                            </p>
                        </div>
                    </div>
                    @forelse($user->posts as $post)
                        <x-post :post="$post" />
                    @empty
                        <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                            <h4 class="fw-bold mb-2">
                                No reviews yet
                            </h4>
                            <p class="mb-0">
                                This user has not posted any reviews yet.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Profile Comments (Right Column) -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h2 class="fw-bold mb-1">
                                Comments
                            </h2>
                            <p class="text-muted mb-0">
                                What users think of {{ $user->username }}
                            </p>
                        </div>
                    </div>    
                    <div class="container">
                        <div class="mt-5 max-w-3xl mx-auto">
                            <x-hub-comments hub-type="user" :hub-id="$user->id" :posts="$posts" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
</x-layout>



