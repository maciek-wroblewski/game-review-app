@props([
    'multiple' => false,
    'accept' => 'image/*',
    'existingMedia' => [],
    'inputName' => 'media_id',
    'previewClass' => 'rounded border bg-dark',
    'previewStyle' => 'height: 80px; width: 80px; object-fit: cover;'
])

<style>
    /* Pure CSS Empty State Logic */
    /* When the gallery is empty (d-none), we ensure the placeholder shows up properly */
    .js-uploader-gallery-container.is-empty .text-muted {
        display: flex !important;
    }
    
    /* Drag and Drop Visual Feedback */
    .js-uploader-gallery-container.drag-over {
        border: 2px dashed #0d6efd !important;
        background-color: #e9ecef !important;
    }
</style>

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
            <span>Drop items here or click to add them</span>
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

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // --- REUSABLE UPLOAD FUNCTION ---
        async function processFiles(files, uploader) {
            const file = files[0]; // For simplicity, handling one at a time to keep your UI logic clean
            if (!file) return;

            const inputName = uploader.dataset.inputName;
            const pClass = uploader.dataset.previewClass;
            const pStyle = uploader.dataset.previewStyle;
            const isMultiple = uploader.dataset.multiple === 'true';
            const gallery = uploader.querySelector('.js-uploader-gallery');
            const container = uploader.querySelector('.js-uploader-gallery-container');
            const spinner = uploader.querySelector('.js-upload-spinner');
            const fileInput = uploader.querySelector('.js-upload-input');

            spinner.classList.remove('d-none');
            fileInput.disabled = true;

            const formData = new FormData(); 
            formData.append('file', file);

            try {
                const res = await fetch("/upload", { 
                    method: 'POST', 
                    body: formData, 
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } 
                });
                
                if (!res.ok) throw new Error('Upload failed');
                const data = await res.json();

                let mediaPreview = data.media.mime_type.startsWith('image/')
                    ? `<img src="${data.media.file_path}" class="${pClass}" style="${pStyle}">`
                    : `<video src="${data.media.file_path}" class="${pClass}" style="${pStyle}" muted></video>`;

                const newItemHtml = `
                    <div class="position-relative media-upload-item">
                        <input type="hidden" name="${inputName}" value="${data.media.id}" class="js-media-hidden-input">
                        ${mediaPreview}
                        <div class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center remove-icon" style="width:20px; height:20px; transform:translate(30%,-30%); cursor:pointer; z-index: 10;">
                            <i class="bi bi-x small"></i>
                        </div>
                    </div>`;

                if (!isMultiple) gallery.innerHTML = '';
                gallery.insertAdjacentHTML('beforeend', newItemHtml);
                
                // UI State Management
                gallery.classList.remove('d-none');
                container.classList.remove('is-empty');
                fileInput.value = ''; 

            } catch (err) {
                console.error(err);
                alert('Failed to upload media.');
            } finally {
                spinner.classList.add('d-none');
                fileInput.disabled = false;
            }
        }

        // --- EVENT DELEGATION ---
        document.addEventListener('click', (e) => {
            // 1. REMOVE MEDIA (Priority 1)
            const removeBtn = e.target.closest('.remove-icon');
            if (removeBtn) {
                e.stopPropagation();
                const uploader = e.target.closest('.js-media-uploader');
                const itemToRemove = e.target.closest('.media-upload-item');
                const gallery = uploader.querySelector('.js-uploader-gallery');
                const container = uploader.querySelector('.js-uploader-gallery-container');

                itemToRemove.remove();

                if (gallery.children.length === 0) {
                    gallery.classList.add('d-none');
                    container.classList.add('is-empty'); // Triggers the CSS placeholder
                }
                return;
            }

            // 2. TRIGGER FILE PICKER (Priority 2)
            const container = e.target.closest('.js-uploader-gallery-container');
            if (container) {
                const uploader = container.closest('.js-media-uploader');
                uploader.querySelector('.js-upload-input').click();
            }
        });

        // 3. HANDLE FILE SELECTION (Input Change)
        document.addEventListener('change', (e) => {
            if (!e.target.matches('.js-upload-input')) return;
            const uploader = e.target.closest('.js-media-uploader');
            processFiles(e.target.files, uploader);
        });

        // 4. DRAG AND DROP LOGIC
        document.addEventListener('dragover', (e) => {
            const container = e.target.closest('.js-uploader-gallery-container');
            if (container) {
                e.preventDefault(); // Necessary to allow drop
                container.classList.add('drag-over');
            }
        });

        document.addEventListener('dragleave', (e) => {
            const container = e.target.closest('.js-uploader-gallery-container');
            if (container) {
                container.classList.remove('drag-over');
            }
        });

        document.addEventListener('drop', (e) => {
            const container = e.target.closest('.js-uploader-gallery-container');
            if (container) {
                e.preventDefault();
                container.classList.remove('drag-over');
                
                const uploader = container.closest('.js-media-uploader');
                const files = e.dataTransfer.files;
                
                if (files.length > 0) {
                    processFiles(files, uploader);
                }
            }
        });
    });
</script>
@endonce
