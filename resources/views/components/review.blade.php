<div class="review-wrapper d-flex align-items-stretch mb-4 shadow-sm rounded overflow-hidden">
    
    {{-- 1. Rating Meter Div --}}
    @if($review->type === 'recommendation')
        @php
            // Determine the color based on the rating score
            $meterColor = $review->rating >= 7 ? 'bg-success' : ($review->rating >= 4 ? 'bg-warning' : 'bg-danger');
            
            // Calculate height percentage (assuming rating is out of 10)
            $heightPercentage = $review->rating * 10;
        @endphp

        <!-- The outer div stretches to match the post height.
             justify-content-end pushes the fill to the bottom so it grows upwards. -->
        <div class="rating-meter-container d-flex flex-column justify-content-end border-end" style="width: 50px; min-width: 50px; background-color:rgba(0, 0, 0, 0.142);">
            <div class="meter-fill {{ $meterColor }} w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold transition-all" 
                 style="height: {{ $heightPercentage }}%;">
                {{-- Optional: Show the number vertically inside the bar --}}
                <span style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 0.85rem;">
                    {{ $review->rating }} / 10
                </span>
            </div>
        </div>
    @endif

    {{-- 2. Post Component Div --}}
    <div class="post-wrapper flex-grow-1 bg-white">
        @if($review->post)
            <x-post :post="$review->post" />
        @else
            <div class="alert alert-warning m-0 h-100 d-flex align-items-center justify-content-center rounded-0 rounded-end">
                This review's associated post is missing.
            </div>
        @endif
    </div>
    
</div>