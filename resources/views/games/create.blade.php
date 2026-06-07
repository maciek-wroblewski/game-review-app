<x-layout headtitle="Create New Game">

    <div class="container py-5 max-w-4xl mx-auto">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">Add a New Game</h2>
                <p class="text-muted mb-0">Create a game profile for the database.</p>
            </div>
            <a href="/games" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <form method="POST" action="/games" enctype="multipart/form-data">
                    @csrf

                    <div class="p-4 bg-light border-bottom">
                        <h5 class="fw-bold mb-3">{{ __('games.game_media') }}</h5>
                        
                        <x-game.image-uploader 
                            name="banner_img"
                            :label="__('games.banner_image')"
                            :default="asset('images/default-banner.jpg')"
                            height="200px"
                            icon="bi-camera-fill"
                            :overlayText="__('games.change_banner')"
                        />

                        <div class="row g-4">
                            <div class="col-sm-6">
                                <x-game.image-uploader 
                                    name="cover_img"
                                    :label="__('games.cover_image')"
                                    :default="asset('images/default-cover.jpg')"
                                    height="300px"
                                    maxWidth="220px"
                                    icon="bi-image"
                                    :overlayText="__('games.change_cover')"
                                    alignmentClass="mx-auto"
                                />
                            </div>

                            <div class="col-sm-6">
                                <x-game.image-uploader 
                                    name="logo"
                                    :label="__('games.game_logo')"
                                    :default="asset('images/default-logo.png')"
                                    height="200px"
                                    maxWidth="200px"
                                    icon="bi-circle-square"
                                    :overlayText="__('games.change_logo')"
                                    accept="image/png,image/webp,image/jpeg"
                                    fit="contain"
                                    :logoMode="true"
                                    alignmentClass="mx-auto"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="p-4 p-lg-5">
                        <h5 class="fw-bold mb-4">{{ __('games.game_information') }}</h5>

                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">{{ __('games.title') }}</label>
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="publisher" class="form-label fw-bold">{{ __('games.publisher') }}</label>
                                <input type="text" class="form-control @error('publisher') is-invalid @enderror" id="publisher" name="publisher" value="{{ old('publisher') }}">
                                @error('publisher') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="release_date" class="form-label fw-bold">{{ __('games.release_date') }}</label>
                                <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date') }}">
                                @error('release_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <x-user-search-selector 
                            name="credits" 
                            :initialUsers="[auth()->user()]" 
                            label="{{ __('games.credits') }} / Authors" 
                            :withRole="true"
                        />

                        <x-game.genre-selector 
                            :genres="$genres" 
                            :selected="old('genres', [])"
                        />

                        <div class="mb-4">
                            <label for="details" class="form-label fw-bold">{{ __('games.description_details') }}</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="6" placeholder="{{ __('games.enter_description') }}">{{ old('details') }}</textarea>
                            @error('details') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-plus-circle me-1"></i> Create Game
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>