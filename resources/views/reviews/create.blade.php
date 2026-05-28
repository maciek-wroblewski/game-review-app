<x-layout headtitle="{{ __('reviews.review_game', ['game' => $game->title ?? __('common.unknown')]) }}">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">{{ __('reviews.review_game', ['game' => $game->title]) }}</h2>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/games/{{ $game->id }}/reviews" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">{{ __('reviews.rating_label') }}</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">{{ __('reviews.choose_rating') }}</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">{{ __('reviews.your_review') }}</label>
                        <textarea class="form-control" id="body" name="body" rows="6" required>{{ old('body') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('reviews.submit_review') }}</button>
                    <a href="/Hub" class="btn btn-outline-secondary ms-2">{{ __('common.cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</x-layout>
