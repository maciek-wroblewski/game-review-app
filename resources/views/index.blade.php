<x-layout headtitle="Home">
    hello bro

   <div class="container mt-4">
        <!-- The Upload Form -->
        <form id="mediaUploadForm" enctype="multipart/form-data">
            @csrf
            <div class="input-group mb-3">
                <input type="file" name="file" class="form-control" id="fileInput" required>
                <button class="btn btn-primary" type="submit" id="uploadBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="uploadSpinner" role="status" aria-hidden="true"></span>
                    Upload
                </button>
            </div>
        </form>

        <!-- Gallery / Preview Container -->
        <div id="mediaPreviewGallery" class="row g-2 mt-3">
            <!-- JavaScript will dynamically inject uploaded images here -->
        </div>

            <script>
        document.getElementById('mediaUploadForm').addEventListener('submit', async function(e) {
        e.preventDefault(); // Stop the page from reloading

        const form = e.target;
        const formData = new FormData(form); // Automatically captures the file input data
        const uploadBtn = document.getElementById('uploadBtn');
        const spinner = document.getElementById('uploadSpinner');
        const gallery = document.getElementById('mediaPreviewGallery');

        // 1. Show loading state
        uploadBtn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            // 2. Send request to Laravel backend
            const response = await fetch("{{ route('upload') }}", {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Tells Laravel it's an AJAX request
                }
            });

            const data = await response.json();

            if (response.ok) {
                // 3. Success! Handle your media record here
                const media = data.media;
                
                // Example HTML insertion using Bootstrap Cards
                const mediaHtml = `
                    <div class="col-md-3 id="media-card-${media.id}">
                        <div class="card h-100 text-center">
                            <img src="${media.file_path}" class="card-img-top img-fluid p-2" style="max-height: 150px; object-fit: cover;" alt="Uploaded media">
                            <div class="card-body p-2">
                                <input type="hidden" name="media_ids[]" value="${media.id}">
                                <span class="badge bg-secondary text-truncate style="max-width: 100%">ID: ${media.id}</span>
                            </div>
                        </div>
                    </div>
                `;
                
                // Append new image to your gallery container
                gallery.insertAdjacentHTML('beforeend', mediaHtml);
                
                // Reset the file input field so they can upload another one
                form.reset();
            } else {
                // Handle validation errors from Laravel
                alert(data.message || 'Something went wrong during the upload.');
            }

        } catch (error) {
            console.error('Error uploading file:', error);
            alert('An unexpected error occurred.');
        } finally {
            // 4. Reset loading state
            uploadBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
    </script>
    </div>
</x-layout>