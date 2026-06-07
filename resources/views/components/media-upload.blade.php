@props([
    'multiple' => false,
    'accept' => 'image/*',
    'existingMedia' => [],
    'inputName' => 'media_id',
    'previewClass' => 'rounded border bg-dark',
    'previewStyle' => 'height: 80px; width: 80px; object-fit: cover;'
])

<div class="js-media-uploader" 
     data-multiple="{{ $multiple ? 'true' : 'false' }}" 
     data-input-name="{{ $inputName }}"
     data-preview-class="{{ $previewClass }}"
     data-preview-style="{{ $previewStyle }}">
    
    <div class="js-uploader-gallery-container border rounded p-2 bg-light d-flex flex-wrap gap-2 align-items-center justify-content-start {{ count($existingMedia) === 0 ? 'is-empty' : '' }}" 
         style="min-height: 100px; cursor: pointer; position: relative;"
         title="Click or Drop media here">
        
        <!-- Placeholder (Centered Text + Icon) -->
        <div class="text-muted small d-none flex-column align-items-center justify-content-center w-100 py-2 text-center pointer-events-none">
            <i class="bi bi-cloud-arrow-up fs-3"></i>
            <span>{{ __('posts.media_upload') }}</span>
        </div>

        <!-- Gallery of existing/uploaded items -->
        <div class="js-uploader-gallery d-flex flex-wrap gap-2 {{ count($existingMedia) === 0 ? 'd-none' : '' }}">
            @foreach($existingMedia as $media)
                <div class="position-relative media-upload-item">
                    <input type="hidden" name="{{ $inputName }}" value="{{ $media->id }}" class="js-media-hidden-input">
                    
                    @if(str_starts_with($media->mime_type, 'image/'))
                        <img src="{{ $media->file_path }}" class="{{ $previewClass }}" style="{{ $previewStyle }}">
                    @elseif(str_starts_with($media->mime_type, 'video/'))
                        <video src="{{ $media->file_path }}" class="{{ $previewClass }}" style="{{ $previewStyle }}" muted></video>
                    @endif
                    
                    <div class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center remove-icon" 
                         style="width: 20px; height: 20px; transform: translate(30%, -30%); cursor: pointer; z-index: 10;">
                        <i class="bi bi-x small"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Hidden Actual Input -->
    <input type="file" class="js-upload-input d-none" accept="{{ $accept }}" {{ $multiple ? 'multiple' : '' }}>
    
    <!-- Loading Spinner Overlay -->
    <div class="js-upload-spinner spinner-border spinner-border-sm text-primary d-none position-absolute top-50 start-50 translate-middle" role="status"></div>
</div>
