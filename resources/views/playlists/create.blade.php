<x-layout headtitle="Create Playlist">
    <div class="container py-5 max-w-2xl mx-auto">
        <div class="mb-4">
            <h1 class="fw-bold mb-1">Create Playlist</h1>
            <p class="text-muted">Group your favorite games into a custom collection.</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/playlists" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <x-input-label for="cover" value="Playlist Cover (Optional)" />
                        <input class="form-control" type="file" id="cover" name="cover" accept="image/*">
                        <x-input-error class="mt-2 text-danger" :messages="$errors->get('cover')" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="name" value="Playlist Name" />
                        <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name')"
                            required autofocus />
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
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" {{
                            old('is_public', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public">Make this playlist public</label>
                        <div class="form-text">Public playlists can be seen by anyone visiting your profile.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">Cancel</a>
                        <x-primary-button>Create Playlist</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>