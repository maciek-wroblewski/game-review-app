<x-layout headtitle="Browse Games">
    <div class="container-xl py-5">

        <section class="trending-section mb-5">
            <div class="d-flex align-items-center justify-content-between">
                <div class="carousel-controls">
                </div>
            </div>
            <div class="trending-wrapper">
                <x-game.trending />
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
                    <x-game.card :game="$game" />
                </div>
                @endforeach
            </div>
            <div class="text-center mt-5">
                <button id="load-more-btn" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                    Load More Games
                </button>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const gridContainer = document.querySelector('.grid-section .row.g-4');
    const loadMoreBtn = document.getElementById('load-more-btn');
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;

    if (!loadMoreBtn || !gridContainer) return;

    loadMoreBtn.addEventListener('click', () => {
        if (isLoading || !hasMore) return;

        isLoading = true;
        currentPage++;
        loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Loading...';
        loadMoreBtn.disabled = true;

        fetch(`/games/load-more?page=${currentPage}`)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                if (data.html) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const newColumns = tempDiv.querySelectorAll('.col-12');
                    newColumns.forEach(col => gridContainer.appendChild(col));
                }

                hasMore = data.hasMore;
                if (!hasMore) {
                    loadMoreBtn.style.display = 'none';
                } else {
                    loadMoreBtn.innerHTML = 'Load More Games';
                }
            })
            .catch(err => {
                console.error('Failed to load games:', err);
                loadMoreBtn.innerHTML = 'Load More Games';
            })
            .finally(() => {
                isLoading = false;
                loadMoreBtn.disabled = false;
            });
    });
});
    </script>


</x-layout>

<style>
    /* Load More Button & Loading State */
    #load-more-btn {
        min-width: 200px;
        transition: all 0.2s ease;
    }

    #load-more-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    #load-more-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.25);
    }

    /* Smooth fade-in for newly loaded cards */
    .col-12 {
        animation: fadeIn 0.4s ease forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>