<x-layout headtitle="{{ $playlist->exists ? __('playlists.edit_playlist') . ' - ' . $playlist->name : __('playlists.create_playlist') }}">
    <style>
        /* Hover Overlay Logic */
        .hover-overlay-container {
            position: relative;
        }
        .hover-overlay-container:hover .hover-overlay {
            opacity: 1 !important;
        }
        .hover-overlay {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
            background: rgba(0, 0, 0, 0.65);
        }
        .playlist-cover-wrapper {
            cursor: pointer;
            aspect-ratio: 1 / 1;
            width: 100%;
            max-width: 100%;
            height: auto;
            background: linear-gradient(135deg, #6c757d, #343a40);
        }
        .playlist-cover-helper {
            max-width: 100%;
        }
        @media (min-width: 768px) {
            .playlist-cover-wrapper {
                width: 200px;
                height: 200px;
            }
            .playlist-cover-helper {
                max-width: 200px;
            }
        }
    </style>

    <div class="container py-5 max-w-2xl mx-auto">
        <div class="mb-4">
            <h1 class="fw-bold mb-1">{{ $playlist->exists ? __('playlists.edit_playlist') : __('playlists.create_playlist') }}</h1>
            <p class="text-muted">{{ $playlist->exists ? __('playlists.edit_description') : __('playlists.create_description') }}</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ $playlist->exists ? '/playlists/' . $playlist->id : '/playlists' }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($playlist->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4">
                        <div class="col-md-auto">
                            <div class="mb-4">
                                <x-input-label value="{{ $playlist->exists ? __('playlists.cover') : __('playlists.cover_optional') }}" class="mb-2 fw-semibold" />
                                
                                <div class="hover-overlay-container playlist-cover-wrapper position-relative rounded overflow-hidden shadow-sm" 
                                     onclick="document.getElementById('coverInput').click()">
                                    
                                    <img src="{{ $playlist->cover ? (str_starts_with($playlist->cover, 'http') ? $playlist->cover : asset('storage/' . $playlist->cover)) : '' }}" 
                                         id="coverPreview" 
                                         class="w-100 h-100 object-fit-cover {{ $playlist->cover ? '' : 'd-none' }}" 
                                         alt="Cover Preview">
                                    
                                    <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                                        <div class="text-center">
                                            <i class="bi bi-image fs-3 mb-1"></i>
                                            <div class="fw-semibold">{{ $playlist->exists ? __('playlists.change_cover') : __('playlists.upload_cover') }}</div>
                                        </div>
                                    </div>
                                    <input type="file" name="cover" id="coverInput" class="d-none" accept="image/*" onchange="previewImage(this, 'coverPreview')">
                                </div>
                                @if($playlist->exists && $playlist->cover)
                                    <div class="form-text mt-1 playlist-cover-helper">{{ __('playlists.keep_current_cover') }}</div>
                                @endif
                                <x-input-error class="mt-2 text-danger" :messages="$errors->get('cover')" />
                            </div>
                        </div>

                        <div class="col-md">
                            <div class="mb-3">
                                <x-input-label for="name" value="{{ __('playlists.name') }}" />
                                <x-text-input id="name" name="name" type="text" class="form-control"
                                    :value="old('name', $playlist->name)" required autofocus />
                                <x-input-error class="mt-2 text-danger" :messages="$errors->get('name')" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="description" value="{{ __('playlists.description_optional') }}" />
                                <textarea id="description" name="description"
                                    class="form-control rounded-md border-gray-300 shadow-sm"
                                    rows="3">{{ old('description', $playlist->description) }}</textarea>
                                <x-input-error class="mt-2 text-danger" :messages="$errors->get('description')" />
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" {{ old('is_public', $playlist->exists ? $playlist->is_public : true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">{{ __('playlists.make_public') }}</label>
                                @if(!$playlist->exists)
                                    <div class="form-text">{{ __('playlists.public_description') }}</div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ $playlist->exists ? '/playlists/' . $playlist->id : 'javascript:history.back()' }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    {{ $playlist->exists ? __('common.save_changes') : __('playlists.create_playlist') }}
                                </button>
                            </div>

                            <x-user-search-selector name="users[]" :initialUsers="$playlist->users ?? [auth()->user()]" label="Playlist Owners" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-layout>
