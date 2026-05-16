@props([
    'multiple' => false,
    'accept' => 'image/*',
    'existingMedia' => [],
    'inputName' => 'media_id',
    'previewClass' => 'rounded border bg-dark', // Default styling
    'previewStyle' => 'height: 80px; width: 80px; object-fit: cover;' // Default styling
])

<div class="js-media-uploader" 
     data-multiple="{{ $multiple ? 'true' : 'false' }}" 
     data-input-name="{{ $inputName }}"
     data-preview-class="{{ $previewClass }}"
     data-preview-style="{{ $previewStyle }}">
    
    <!-- Gallery / Preview Container -->
    <div class="js-uploader-gallery-container mb-3 {{ count($existingMedia) === 0 ? 'd-none' : '' }}">
        <label class="form-label fw-bold text-muted small">Preview</label>
        <div class="js-uploader-gallery row g-2">
            @foreach($existingMedia as $media)
                <div class="col-auto position-relative media-upload-item">
                    <input type="hidden" name="{{ $inputName }}" value="{{ $media->id }}" class="js-media-hidden-input">
                    
                    @if(str_starts_with($media->mime_type, 'image/'))
                        <img src="{{ $media->file_path }}" class="{{ $previewClass }}" style="{{ $previewStyle }}">
                    @elseif(str_starts_with($media->mime_type, 'video/'))
                        <video src="{{ $media->file_path }}" class="{{ $previewClass }}" style="{{ $previewStyle }}" muted></video>
                    @endif
                    
                    <div class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center remove-icon" style="width: 20px; height: 20px; transform: translate(30%, -30%); cursor: pointer;">
                        <i class="bi bi-x small"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Upload Input -->
    <div class="input-group">
        <input type="file" class="js-upload-input form-control" accept="{{ $accept }}">
        <button class="js-btn-upload btn btn-secondary" type="button">
            <span class="js-upload-spinner spinner-border spinner-border-sm d-none"></span> Attach
        </button>
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.addEventListener('click', (e) => {
            // Remove Media
            if (e.target.closest('.remove-icon')) {
                const uploader = e.target.closest('.js-media-uploader');
                e.target.closest('.media-upload-item')?.remove();
                
                if (uploader.querySelector('.js-uploader-gallery').children.length === 0) {
                    uploader.querySelector('.js-uploader-gallery-container').classList.add('d-none');
                }
            }

            // Upload Media
            if (e.target.closest('.js-btn-upload')) {
                const uploader = e.target.closest('.js-media-uploader');
                const uploadInput = uploader.querySelector('.js-upload-input');
                const file = uploadInput?.files[0];
                if (!file) return;

                const btnUpload = e.target.closest('.js-btn-upload');
                const isMultiple = uploader.dataset.multiple === 'true';
                const inputName = uploader.dataset.inputName;
                const pClass = uploader.dataset.previewClass;
                const pStyle = uploader.dataset.previewStyle;
                
                const galleryContainer = uploader.querySelector('.js-uploader-gallery-container');
                const gallery = uploader.querySelector('.js-uploader-gallery');

                btnUpload.disabled = true;
                uploader.querySelector('.js-upload-spinner').classList.remove('d-none');

                const formData = new FormData(); 
                formData.append('file', file);

                fetch("/upload", { 
                    method: 'POST', 
                    body: formData, 
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } 
                })
                .then(async res => {
                    if (!res.ok) throw new Error('Upload failed');
                    return res.json();
                })
                .then(data => {
                    let mediaPreview = data.media.mime_type.startsWith('image/')
                        ? `<img src="${data.media.file_path}" class="${pClass}" style="${pStyle}">`
                        : `<video src="${data.media.file_path}" class="${pClass}" style="${pStyle}" muted></video>`;

                    const newItemHtml = `
                        <div class="col-auto position-relative media-upload-item">
                            <input type="hidden" name="${inputName}" value="${data.media.id}" class="js-media-hidden-input">
                            ${mediaPreview}
                            <div class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center remove-icon" style="width:20px; height:20px; transform:translate(30%,-30%); cursor:pointer;">
                                <i class="bi bi-x small"></i>
                            </div>
                        </div>`;

                    if (!isMultiple) gallery.innerHTML = '';
                    
                    gallery.insertAdjacentHTML('beforeend', newItemHtml);
                    galleryContainer.classList.remove('d-none');
                    uploadInput.value = '';

                    // FIRES A CUSTOM EVENT WITH THE UPLOADED FILE DATA
                    uploader.dispatchEvent(new CustomEvent('media-uploaded', {
                        bubbles: true,
                        detail: { media: data.media }
                    }));
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to upload media.');
                })
                .finally(() => { 
                    btnUpload.disabled = false; 
                    uploader.querySelector('.js-upload-spinner').classList.add('d-none'); 
                });
            }
        });
    });
</script>
@endonce