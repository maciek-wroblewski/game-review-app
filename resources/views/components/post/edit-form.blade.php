@props(['post'])

<div class="js-edit-mode d-none border rounded p-3 bg-light">
    <!-- Post Content -->
    <div class="mb-3">
        <label class="form-label fw-bold text-muted small">Post Content</label>
        <textarea class="js-edit-textarea form-control" rows="4">{{ $post->body }}</textarea>
    </div>

    <!-- Attached Media -->
    <div class="mb-3">
        <label class="form-label fw-bold text-muted small">Attached Media</label>
        <div class="js-edit-media-gallery row g-2">
            @foreach($post->media as $media)
                <div class="col-auto position-relative media-edit-item" data-media-id="{{ $media->id }}">
                    @if(str_starts_with($media->mime_type, 'image/'))
                        <img src="{{ $media->file_path }}" class="rounded border bg-dark" style="height: 80px; width: 80px; object-fit: cover;">
                    @elseif(str_starts_with($media->mime_type, 'video/'))
                        <video src="{{ $media->file_path }}" class="rounded border bg-dark" style="height: 80px; width: 80px; object-fit: cover;" muted></video>
                    @endif
                    <div class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center remove-icon pointer-events-none" style="width: 20px; height: 20px; transform: translate(30%, -30%);"><i class="bi bi-x small"></i></div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Upload Media -->
    <div class="mb-3 border-bottom pb-3">
        <div class="input-group">
            <input type="file" class="js-upload-input form-control" accept="image/*,video/mp4">
            <button class="js-btn-upload btn btn-secondary" type="button"><span class="js-upload-spinner spinner-border spinner-border-sm d-none"></span> Attach</button>
        </div>
    </div>

    <!-- Toggles & Action Buttons Row -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
        <!-- Toggles (Left Side) -->
        <div class="d-flex gap-3">
            <!-- Spoiler Toggle -->
            <div class="form-check form-switch mb-0">
                <input class="form-check-input js-edit-spoiler" 
                       type="checkbox" 
                       id="isSpoiler-{{ $post->id }}" 
                       {{ $post->is_spoiler ? 'checked' : '' }}>
                <label class="form-check-label small user-select-none" for="isSpoiler-{{ $post->id }}">
                    <i class="bi bi-eye-slash-fill text-warning me-1"></i> Spoiler
                </label>
            </div>

            <!-- Locked Toggle -->
            <div class="form-check form-switch mb-0">
                <input class="form-check-input js-edit-locked" 
                       type="checkbox" 
                       id="isLocked-{{ $post->id }}" 
                       {{ $post->is_locked ? 'checked' : '' }}>
                <label class="form-check-label small user-select-none" for="isLocked-{{ $post->id }}">
                    <i class="bi bi-lock-fill text-danger me-1"></i> Lock
                </label>
            </div>
        </div>

        <!-- Buttons (Right Side) -->
        <div class="d-flex gap-2 justify-content-end">
            <button class="js-btn-cancel btn btn-outline-secondary">Cancel</button>
            <button class="js-btn-save btn btn-primary px-4"><span class="js-save-spinner spinner-border spinner-border-sm d-none"></span> Save Changes</button>
        </div>
    </div>
</div>