<x-layout headtitle="Edit {{ $playlist->name }}">
    <div class="container py-5 max-w-2xl mx-auto">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-1">Edit Playlist</h1>
                <p class="text-muted">Update your collection details.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/playlists/{{ $playlist->id }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <x-input-label for="name" value="Playlist Name" />
                        <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name', $playlist->name)" required autofocus />
                        <x-input-error class="mt-2 text-danger" :messages="$errors->get('name')" />
                    </div>

                    <div class="mb-3">
                        <x-input-label for="description" value="Description (Optional)" />
                        <textarea id="description" name="description" class="form-control rounded-md border-gray-300 shadow-sm" rows="3">{{ old('description', $playlist->description) }}</textarea>
                        <x-input-error class="mt-2 text-danger" :messages="$errors->get('description')" />
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" {{ old('is_public', $playlist->is_public) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public">Make this playlist public</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/playlists/{{ $playlist->id }}" class="btn btn-outline-secondary">Cancel</a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>