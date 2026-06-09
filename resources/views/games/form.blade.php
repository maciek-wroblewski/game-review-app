<x-layout headtitle="{{ $game->exists ? __('games.edit_game_profile') . ' - ' . $game->title : 'Create New Game' }}">

    <div class="container py-5 max-w-4xl mx-auto">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">{{ $game->exists ? __('games.edit_game_profile') : 'Add a New Game' }}</h2>
                <p class="text-muted mb-0">
                    @if($game->exists)
                        {{ __('games.update_info_media') }} {{ $game->title }}
                    @else
                        Create a game profile for the database.
                    @endif
                </p>
            </div>
            <a href="{{ $game->exists ? '/games/' . $game->id : '/games' }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> {{ $game->exists ? __('games.back_to_game') : 'Cancel' }}
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <form method="POST" action="{{ $game->exists ? '/games/' . $game->id : '/games' }}" enctype="multipart/form-data">
                    @csrf
                    @if($game->exists)
                        @method('PATCH')
                    @endif

                    <div class="p-4 bg-light border-bottom">
                        <h5 class="fw-bold mb-3">{{ __('games.game_media') }}</h5>
                        
                        <x-game.image-uploader 
                            name="banner_img"
                            :label="__('games.banner_image')"
                            :value="$game->banner_img"
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
                                    :value="$game->cover_img"
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
                                    :value="$game->logo"
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
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $game->title) }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="publisher" class="form-label fw-bold">{{ __('games.publisher') }}</label>
                                <input type="text" class="form-control @error('publisher') is-invalid @enderror" id="publisher" name="publisher" value="{{ old('publisher', $game->publisher) }}">
                                @error('publisher') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="release_date" class="form-label fw-bold">{{ __('games.release_date') }}</label>
                                <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date', $game->release_date ? $game->release_date->format('Y-m-d') : '') }}">
                                @error('release_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <x-user-search-selector 
                            name="credits" 
                            :initialUsers="$game->exists ? $game->credits : [auth()->user()]" 
                            label="{{ __('games.credits') }} / Authors" 
                            :withRole="true"
                        />

                        <x-game.genre-selector 
                            :genres="$genres" 
                            :selected="old('genres', $game->exists ? $game->genres : [])"
                        />

                        <div class="mb-4">
                            <label for="details" class="form-label fw-bold">{{ __('games.description_details') }}</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="6" placeholder="{{ __('games.enter_description') }}">{{ old('details', $game->details) }}</textarea>
                            @error('details') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ $game->exists ? '/games/' . $game->id : '/games' }}" class="btn btn-light">{{ __('games.cancel') }}</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                @if($game->exists)
                                    <i class="bi bi-check-circle me-1"></i> {{ __('games.save_changes') }}
                                @else
                                    <i class="bi bi-plus-circle me-1"></i> Create Game
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
