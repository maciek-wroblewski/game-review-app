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
    </style>

    <div class="container py-5 max-w-4xl mx-auto">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">Edit Game Profile</h2>
                <p class="text-muted mb-0">Update information and media for {{ $game->title }}</p>
            </div>
            <a href="/games/{{ $game->id }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Game
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <form method="POST" action="/games/{{ $game->id }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="p-4 bg-light border-bottom">
                        <h5 class="fw-bold mb-3">Game Media</h5>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Banner Image</label>
                            <div class="image-edit-container" style="height: 200px;" onclick="document.getElementById('bannerInput').click()">
                                <img src="{{ $game->banner_img ?? asset('images/default-banner.jpg') }}" id="bannerPreview" class="object-fit-cover" alt="Banner">
                                <div class="image-edit-overlay">
                                    <i class="bi bi-camera-fill fs-2 mb-2"></i>
                                    <span class="fw-bold">Change Banner</span>
                                </div>
                                <input type="file" name="banner_img" id="bannerInput" class="d-none" accept="image/*" onchange="previewImage(this, 'bannerPreview')">
                            </div>
                            @error('banner_img') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-4">
                            <div class="col-sm-6">
                                <label class="form-label text-muted small fw-bold">Cover Image (Vertical)</label>
                                <div class="image-edit-container mx-auto" style="height: 300px; max-width: 220px;" onclick="document.getElementById('coverInput').click()">
                                    <img src="{{ $game->cover_img ?? asset('images/default-cover.jpg') }}" id="coverPreview" class="object-fit-cover" alt="Cover">
                                    <div class="image-edit-overlay">
                                        <i class="bi bi-image fs-3 mb-2"></i>
                                        <span class="fw-bold">Change Cover</span>
                                    </div>
                                    <input type="file" name="cover_img" id="coverInput" class="d-none" accept="image/*" onchange="previewImage(this, 'coverPreview')">
                                </div>
                                @error('cover_img') <div class="text-danger text-center small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label text-muted small fw-bold">Game Logo (Transparent PNG)</label>
                                <div class="image-edit-container mx-auto" style="height: 200px; max-width: 200px; background-image: radial-gradient(#ccc 1px, transparent 0); background-size: 15px 15px;" onclick="document.getElementById('logoInput').click()">
                                    <img src="{{ $game->logo ?? asset('images/default-logo.png') }}" id="logoPreview" class="object-fit-cover" style="object-fit: contain; padding: 1rem;" alt="Logo">
                                    <div class="image-edit-overlay">
                                        <i class="bi bi-circle-square fs-3 mb-2"></i>
                                        <span class="fw-bold">Change Logo</span>
                                    </div>
                                    <input type="file" name="logo" id="logoInput" class="d-none" accept="image/png,image/webp,image/jpeg" onchange="previewImage(this, 'logoPreview')">
                                </div>
                                @error('logo') <div class="text-danger text-center small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="p-4 p-lg-5">
                        <h5 class="fw-bold mb-4">Game Information</h5>

                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Title</label>
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $game->title) }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="publisher" class="form-label fw-bold">Publisher</label>
                                <input type="text" class="form-control @error('publisher') is-invalid @enderror" id="publisher" name="publisher" value="{{ old('publisher', $game->publisher) }}">
                                @error('publisher') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="release_date" class="form-label fw-bold">Release Date</label>
                                <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date', $game->release_date ? $game->release_date->format('Y-m-d') : '') }}">
                                @error('release_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="details" class="form-label fw-bold">Description / Details</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="6" placeholder="Enter game description...">{{ old('details', $game->details) }}</textarea>
                            @error('details') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="/games/{{ $game->id }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
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
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-layout>