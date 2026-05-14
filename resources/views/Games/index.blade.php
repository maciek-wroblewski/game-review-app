<x-layout headtitle="Browse Games">
    <div class="container-xl py-5">
        
        <!-- Trending Section -->
        <section class="trending-section mb-5">
            <div class="d-flex align-items-center justify-content-between">
                <div class="carousel-controls">
                </div>
            </div>
            <div class="trending-wrapper">
                <x-trending-games />
            </div>
        </section>

        <hr class="my-5 opacity-10">

        <section class="grid-section">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="fw-bold mb-0">🎮 Explore Games</h2>
                <span class="text-muted small">{{ $games->total() }} titles found</span>
            </div>

            <div class="row g-4">
                @foreach($games as $game)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <x-game-card :game="$game" />
                    </div>
                @endforeach
            </div>

            <!-- Enhanced Pagination Section -->
            <div class="pagination-container mt-5">
                <div class="pagination-wrapper shadow-sm">
                    {{ $games->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </section>
    </div>
</x-layout>

<style>
    /* Section Headers */
    h2 {
        letter-spacing: -0.5px;
        color: #1a1d20;
    }

    /* Container refinement */
    .container-xl {
        max-width: 1400px; /* Limits width on ultra-wide screens */
    }

    /* Smooth transition for game cards */
    .row .col-12 {
        transition: transform 0.2s ease;
    }

    /* Pagination Redesign */
    .pagination-container {
        display: flex;
        justify-content: center;
        padding-bottom: 2rem;
    }

    .pagination-wrapper {
        background: #ffffff;
        border-radius: 50px; /* Pill shape */
        padding: 0.5rem 1.5rem;
        border: 1px solid #edf2f7;
        display: inline-block;
    }

    .pagination-wrapper nav {
        margin: 0;
    }

    .pagination-wrapper .pagination {
        margin: 0;
        gap: 0.25rem;
        border: none;
    }

    .pagination-wrapper .page-item {
        border: none;
    }

    .pagination-wrapper .page-link {
        border: none;
        background: transparent;
        color: #4a5568;
        font-weight: 600;
        border-radius: 50% !important; /* Circular buttons */
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        transition: all 0.2s ease;
    }

    /* Active State */
    .pagination-wrapper .page-item.active .page-link {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    /* Hover State */
    .pagination-wrapper .page-item:not(.active) .page-link:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
        transform: translateY(-2px);
    }

    /* Disabled State */
    .pagination-wrapper .page-item.disabled .page-link {
        opacity: 0.4;
        background: transparent;
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .pagination-wrapper {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }
    }
</style>