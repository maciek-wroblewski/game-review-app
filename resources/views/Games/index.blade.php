<x-layout headtitle="Browse Games">
    <x-trending-games />
    <!-- Game Grid -->
    <div class="row g-4">
        @foreach($games as $game)
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <x-game-card :game="$game" />
            </div>
        @endforeach
    </div>

<!-- Pagination Section -->
    <div class="d-flex justify-content-center mt-5 mb-4">
        <div class="pagination-wrapper bg-white border">
            {{ $games->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-layout>

<style>
    .pagination-wrapper {
        border-radius: 0.75rem; /* ~12px */
        padding: 1% 2%;
        box-shadow: 0 0.25rem 1.25rem rgba(0, 0, 0, 0.04);
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        margin-bottom: 0;
        display: flex;
        width: 100%;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: space-between;
    }
    .pagination-wrapper .pagination li {
        flex-grow: 1;
    }

    .pagination-wrapper nav {
        width: 100%;
    }

    .pagination-wrapper .page-link {
        border: 0.125rem solid transparent; /* ~2px */
        color: #6c757d;
        font-weight: 600;
        background-color: #f8f9fa; 
        border-radius: 0.5rem !important; /* ~8px */
        
        min-width: 2.6rem; 
        height: 2.6rem;
        padding: 0 1.2em;
        
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-size: 1rem; /* Standard root size */
    }

    .pagination-wrapper .page-item.active .page-link {
        background-color: #ffffff;
        color: #0d6efd; 
        border: 0.125rem solid #0d6efd; 
        box-shadow: 0 0.25rem 0.75rem rgba(13, 110, 253, 0.15); 
    }

    .pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #ffffff;
        color: #0a58ca;
        border-color: #b6d4fe; 
        transform: translateY(-0.125rem); 
    }

    .pagination-wrapper .page-item.disabled .page-link {
        background-color: #ffffff;
        color: #dee2e6;
        opacity: 0.6;
        pointer-events: none;
    }
</style>