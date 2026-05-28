<x-layout headtitle="Create Playlist">
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
    </style>

    <div class="container py-5 max-w-2xl mx-auto">
        <div class="mb-4">
            <h1 class="fw-bold mb-1">Create Playlist</h1>
            <p class="text-muted">Group your favorite games into a custom collection.</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/playlists" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <x-input-label value="Playlist Cover (Optional)" class="mb-2 fw-semibold" />
                        
                        <div class="hover-overlay-container position-relative rounded overflow-hidden shadow-sm" 
                             style="cursor: pointer; height: 200px; background: linear-gradient(135deg, #6c757d, #343a40);" 
                             onclick="document.getElementById('coverInput').click()">
                            
                            <img src="" id="coverPreview" class="w-100 h-100 object-fit-cover d-none" alt="Cover Preview">
                            
                            <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                                <div class="text-center">
                                    <i class="bi bi-image fs-3 mb-1"></i>
                                    <div class="fw-semibold">Upload Cover</div>
                                </div>
                            </div>
                            <input type="file" name="cover" id="coverInput" class="d-none" accept="image/*" onchange="previewImage(this, 'coverPreview')">
                        </div>
                        <x-input-error class="mt-2 text-danger" :messages="$errors->get('cover')" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="name" value="Playlist Name" />
                        <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2 text-danger" :messages="$errors->get('name')" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="description" value="Description (Optional)" />
                        <textarea id="description" name="description"
                            class="form-control rounded-md border-gray-300 shadow-sm"
                            rows="3">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2 text-danger" :messages="$errors->get('description')" />
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public">Make this playlist public</label>
                        <div class="form-text">Public playlists can be seen by anyone visiting your profile.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            Create Playlist
                        </button>
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