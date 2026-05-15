<x-layout headtitle="Review {{ $game->title ?? 'Game' }}">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Review: {{ $game->title }}</h2>
                
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
                        <label for="rating" class="form-label">Rating (1-10)</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">Choose a rating...</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Your Review</label>
                        <textarea class="form-control" id="body" name="body" rows="6" required>{{ old('body') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Review</button>
                    <a href="/Hub" class="btn btn-outline-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</x-layout>
