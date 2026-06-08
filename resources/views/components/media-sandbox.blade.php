<div class="card border shadow-sm mt-5 overflow-hidden">
    <div class="card-header bg-light p-3 sandbox-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#sandboxCollapse" aria-expanded="false" style="cursor: pointer;">
        <span class="fw-bold text-secondary"><i class="bi bi-file-earmark-arrow-up me-2"></i>Media Upload Sandbox</span>
        <i class="bi bi-chevron-down text-secondary"></i>
    </div>
    <div class="collapse" id="sandboxCollapse">
        <div class="card-body bg-white">
            <p class="text-muted small">This sandbox area allows uploading files and previewing uploads asynchronously.</p>
            <form id="mediaUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="input-group mb-3">
                    <input type="file" name="file" class="form-control" id="fileInput" required>
                    <button class="btn btn-primary" type="submit" id="uploadBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="uploadSpinner" role="status" aria-hidden="true"></span>
                        {{ __('home.upload') }}
                    </button>
                </div>
            </form>

            <div id="mediaPreviewGallery" class="row g-2 mt-3">
                <!-- JavaScript will dynamically inject uploaded images here -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadForm = document.getElementById('mediaUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);
                const uploadBtn = document.getElementById('uploadBtn');
                const spinner = document.getElementById('uploadSpinner');
                const gallery = document.getElementById('mediaPreviewGallery');

                uploadBtn.disabled = true;
                spinner.classList.remove('d-none');

                try {
                    const response = await fetch("{{ route('upload') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        const media = data.media;
                        const mediaHtml = `
                            <div class="col-md-3" id="media-card-${media.id}">
                                <div class="card h-100 text-center">
                                    <img src="${media.file_path}" class="card-img-top img-fluid p-2" style="max-height: 150px; object-fit: cover;" alt="Uploaded media">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="media_ids[]" value="${media.id}">
                                        <span class="badge bg-secondary text-truncate" style="max-width: 100%">ID: ${media.id}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        gallery.insertAdjacentHTML('beforeend', mediaHtml);
                        form.reset();
                    } else {
                        alert(data.message || '{{ __('home.something_went_wrong_upload') }}');
                    }
                } catch (error) {
                    console.error('Error uploading file:', error);
                    alert('{{ __('home.unexpected_error') }}');
                } finally {
                    uploadBtn.disabled = false;
                    spinner.classList.add('d-none');
                }
            });
        }
    });
</script>
