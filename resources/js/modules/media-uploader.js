export default class MediaUploader {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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
                    container.classList.add('is-empty');
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
            this.processFiles(e.target.files, uploader);
        });

        // 4. DRAG AND DROP LOGIC
        document.addEventListener('dragover', (e) => {
            const container = e.target.closest('.js-uploader-gallery-container');
            if (container) {
                e.preventDefault();
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
                    this.processFiles(files, uploader);
                }
            }
        });
    }

    async processFiles(files, uploader) {
        const file = files[0];
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
            const res = await fetch('/upload', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
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
}
