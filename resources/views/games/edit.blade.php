<x-layout headtitle="Edit {{ $game->title }}">
    
    <style>
        .image-edit-container {
            position: relative;
            overflow: hidden;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            cursor: pointer;
        }
        
        .image-edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        .image-edit-container:hover .image-edit-overlay {
            opacity: 1;
        }

        .object-fit-cover {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        .genre-badge {
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .genre-badge:hover {
            opacity: 0.8;
            text-decoration: line-through;
        }
    </style>

    <div class="container py-5 max-w-4xl mx-auto">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">{{ __('games.edit_game_profile') }}</h2>
                <p class="text-muted mb-0">{{ __('games.update_info_media') }} {{ $game->title }}</p>
            </div>
            <a href="/games/{{ $game->id }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('games.back_to_game') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <form method="POST" action="/games/{{ $game->id }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="p-4 bg-light border-bottom">
                        <h5 class="fw-bold mb-3">{{ __('games.game_media') }}</h5>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">{{ __('games.banner_image') }}</label>
                            <div class="image-edit-container" style="height: 200px;" onclick="document.getElementById('bannerInput').click()">
                                <img src="{{ $game->banner_img ?? asset('images/default-banner.jpg') }}" id="bannerPreview" class="object-fit-cover" alt="Banner">
                                <div class="image-edit-overlay">
                                    <i class="bi bi-camera-fill fs-2 mb-2"></i>
                                    <span class="fw-bold">{{ __('games.change_banner') }}</span>
                                </div>
                                <input type="file" name="banner_img" id="bannerInput" class="d-none" accept="image/*" onchange="previewImage(this, 'bannerPreview')">
                            </div>
                            @error('banner_img') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-4">
                            <div class="col-sm-6">
                                <label class="form-label text-muted small fw-bold">{{ __('games.cover_image') }}</label>
                                <div class="image-edit-container mx-auto" style="height: 300px; max-width: 220px;" onclick="document.getElementById('coverInput').click()">
                                    <img src="{{ $game->cover_img ?? asset('images/default-cover.jpg') }}" id="coverPreview" class="object-fit-cover" alt="Cover">
                                    <div class="image-edit-overlay">
                                        <i class="bi bi-image fs-3 mb-2"></i>
                                        <span class="fw-bold">{{ __('games.change_cover') }}</span>
                                    </div>
                                    <input type="file" name="cover_img" id="coverInput" class="d-none" accept="image/*" onchange="previewImage(this, 'coverPreview')">
                                </div>
                                @error('cover_img') <div class="text-danger text-center small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label text-muted small fw-bold">{{ __('games.game_logo') }}</label>
                                <div class="image-edit-container mx-auto" style="height: 200px; max-width: 200px; background-image: radial-gradient(#ccc 1px, transparent 0); background-size: 15px 15px;" onclick="document.getElementById('logoInput').click()">
                                    <img src="{{ $game->logo ?? asset('images/default-logo.png') }}" id="logoPreview" class="object-fit-cover" style="object-fit: contain; padding: 1rem;" alt="Logo">
                                    <div class="image-edit-overlay">
                                        <i class="bi bi-circle-square fs-3 mb-2"></i>
                                        <span class="fw-bold">{{ __('games.change_logo') }}</span>
                                    </div>
                                    <input type="file" name="logo" id="logoInput" class="d-none" accept="image/png,image/webp,image/jpeg" onchange="previewImage(this, 'logoPreview')">
                                </div>
                                @error('logo') <div class="text-danger text-center small mt-1">{{ $message }}</div> @enderror
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

                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ __('games.genres') }}</label>
                            
                            <div id="activeGenres" class="d-flex flex-wrap gap-2 mb-3">
                                </div>

                            <div class="position-relative">
                                <input type="text" id="genreSearchInput" class="form-control" placeholder="{{ __('games.search_add_genre') }}" autocomplete="off">
                                <div id="genreDropdown" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                                    </div>
                            </div>

                            <div id="hiddenGenreInputs"></div>
                            
                            <small class=\"text-muted mt-2 d-block\">{{ __('games.click_remove_genre') }}</small>
                            @error('genres') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('genres.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="details" class="form-label fw-bold">{{ __('games.description_details') }}</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="6" placeholder="{{ __('games.enter_description') }}">{{ old('details', $game->details) }}</textarea>
                            @error('details') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="/games/{{ $game->id }}" class="btn btn-light">{{ __('games.cancel') }}</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-check-circle me-1"></i> {{ __('games.save_changes') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- Image Preview Logic ---
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- Interactive Genre Selection Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            const allGenres = @json($genres);
            const rawInitialGenres = @json(old('genres', $game->genres->pluck('id')->toArray()));
            
            // Array of objects { id: int|string, name: string }
            let selectedGenres = [];

            // Parse initial genres (handles both numeric IDs from DB and strings from failed validation)
            if (rawInitialGenres) {
                rawInitialGenres.forEach(item => {
                    let isNum = !isNaN(item) && !isNaN(parseFloat(item));
                    if (isNum) {
                        let g = allGenres.find(g => g.id == item);
                        if (g) selectedGenres.push({ id: g.id, name: g.name });
                    } else {
                        // It was a newly typed string that bounced back from validation
                        selectedGenres.push({ id: item, name: item });
                    }
                });
            }

            const searchInput = document.getElementById('genreSearchInput');
            const dropdown = document.getElementById('genreDropdown');
            const activeContainer = document.getElementById('activeGenres');
            const hiddenContainer = document.getElementById('hiddenGenreInputs');

            function render() {
                activeContainer.innerHTML = '';
                hiddenContainer.innerHTML = '';

                selectedGenres.forEach(genre => {
                    // Create Badge
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-secondary genre-badge d-inline-flex align-items-center py-2 px-3 fs-6';
                    badge.innerHTML = `${genre.name} <i class="bi bi-x-circle ms-2"></i>`;
                    badge.onclick = () => removeGenre(genre.id);
                    activeContainer.appendChild(badge);

                    // Create Hidden Input
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'genres[]';
                    hiddenInput.value = genre.id; // Either the numeric ID or the exact string
                    hiddenContainer.appendChild(hiddenInput);
                });

                filterDropdown(); 
            }

            function filterDropdown() {
                const query = searchInput.value.trim().toLowerCase();
                dropdown.innerHTML = '';
                let exactMatchFound = false;

                if (query) {
                    dropdown.classList.remove('d-none');
                    
                    // Filter existing genres
                    const availableGenres = allGenres.filter(g => 
                        !selectedGenres.some(sg => sg.name.toLowerCase() === g.name.toLowerCase()) && 
                        g.name.toLowerCase().includes(query)
                    );

                    availableGenres.forEach(genre => {
                        if (genre.name.toLowerCase() === query) exactMatchFound = true;
                        createDropdownItem(genre.name, () => addGenre(genre.id, genre.name));
                    });

                    // Add "Create new" option if no exact match is found and it's not already selected
                    const isAlreadySelected = selectedGenres.some(sg => sg.name.toLowerCase() === query);
                    if (!exactMatchFound && !isAlreadySelected) {
                        const newName = searchInput.value.trim();
                        createDropdownItem(`Add new: "${newName}"`, () => addGenre(newName, newName), 'text-primary fw-bold');
                    }
                } else {
                    dropdown.classList.add('d-none');
                }
            }

            function createDropdownItem(text, onClick, extraClasses = '') {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `list-group-item list-group-item-action ${extraClasses}`;
                btn.textContent = text;
                btn.onclick = onClick;
                dropdown.appendChild(btn);
            }

            window.addGenre = function(id, name) {
                if (!selectedGenres.some(g => g.name.toLowerCase() === name.toLowerCase())) {
                    selectedGenres.push({ id, name });
                    searchInput.value = '';
                    render();
                    searchInput.focus();
                }
            }

            window.removeGenre = function(id) {
                selectedGenres = selectedGenres.filter(g => g.id !== id);
                render();
            }

            // Keyboard support for hitting "Enter" to add the first item
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (dropdown.children.length > 0) {
                        dropdown.children[0].click(); // Click the top result automatically
                    }
                }
            });

            searchInput.addEventListener('input', filterDropdown);
            searchInput.addEventListener('focus', filterDropdown);

            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('d-none');
                }
            });

            render();
        });
    </script>
</x-layout>