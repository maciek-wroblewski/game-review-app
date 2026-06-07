@props([
    'name',
    'label',
    'value' => null,
    'default',
    'height' => '200px',
    'maxWidth' => 'none',
    'icon' => 'bi-camera-fill',
    'overlayText' => 'Change Image',
    'accept' => 'image/*',
    'fit' => 'cover',
    'logoMode' => false,
    'alignmentClass' => ''
])

@once
<style>
    .image-edit-container {
        cursor: pointer;
    }
    .image-edit-container:hover .image-edit-overlay {
        opacity: 1 !important;
    }
</style>

<script>
    function previewComponentImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endonce

<div class="mb-4 {{ $alignmentClass }}">
    <label class="form-label text-muted small fw-bold">{{ $label }}</label>
    
    <div class="image-edit-container position-relative overflow-hidden"
         style="
            height: {{ $height }};
            max-width: {{ $maxWidth }};
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background-color: #f8f9fa;
            @if($logoMode)
                background-image: radial-gradient(#ccc 1px, transparent 0);
                background-size: 15px 15px;
            @endif
         "
         onclick="document.getElementById('{{ $name }}Input').click()">
         
        <img src="{{ $value ?? $default }}" 
             id="{{ $name }}Preview" 
             class="w-100 h-100" 
             style="
                object-fit: {{ $fit }};
                @if($logoMode) padding: 1rem; @endif
             " 
             alt="{{ $label }}">
             
        <div class="image-edit-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 text-white d-flex flex-column align-items-center justify-content-center opacity-0"
             style="transition: opacity 0.2s ease-in-out; pointer-events: none;">
            <i class="bi {{ $icon }} fs-3 mb-2"></i>
            <span class="fw-bold small">{{ $overlayText }}</span>
        </div>
        
        <input type="file" 
               name="{{ $name }}" 
               id="{{ $name }}Input" 
               class="d-none" 
               accept="{{ $accept }}" 
               onchange="previewComponentImage(this, '{{ $name }}Preview')">
    </div>
    @error($name) <div class="text-danger small mt-1 {{ $logoMode ? 'text-center' : '' }}">{{ $message }}</div> @enderror
</div>
