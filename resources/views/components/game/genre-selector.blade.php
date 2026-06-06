@props([
    'genres',
    'selected' => []
])

@php
    // Normalize the selected genres to an array of objects with 'id' and 'name'
    $selectedNormalized = collect($selected)->map(function ($item) use ($genres) {
        if (is_object($item)) {
            return ['id' => $item->id, 'name' => $item->name];
        }
        
        $genreModel = $genres->first(fn($g) => $g->id == $item);
        if ($genreModel) {
            return ['id' => $genreModel->id, 'name' => $genreModel->name];
        }
        
        return ['id' => $item, 'name' => $item];
    })->values();
@endphp

<div class="mb-4 js-genre-selector-container">
    <label class="form-label fw-bold">{{ __('games.genres') }}</label>
    
    <div class="js-active-genres d-flex flex-wrap gap-2 mb-3"></div>

    <div class="position-relative">
        <input type="text" class="js-genre-search-input form-control" placeholder="{{ __('games.search_add_genre') }}" autocomplete="off">
        <div class="js-genre-dropdown list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
    </div>

    <div class="js-hidden-genre-inputs"></div>
    
    <small class="text-muted mt-2 d-block">{{ __('games.click_remove_genre') }}</small>
    @error('genres') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    @error('genres.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.js-genre-selector-container');
        if (!container) return;

        const allGenres = @json($genres);
        let selectedGenres = @json($selectedNormalized);

        const searchInput = container.querySelector('.js-genre-search-input');
        const dropdown = container.querySelector('.js-genre-dropdown');
        const activeContainer = container.querySelector('.js-active-genres');
        const hiddenContainer = container.querySelector('.js-hidden-genre-inputs');

        function render() {
            activeContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';

            selectedGenres.forEach(genre => {
                // Create Badge
                const badge = document.createElement('span');
                badge.className = 'badge bg-secondary genre-badge d-inline-flex align-items-center py-2 px-3 fs-6';
                badge.style.cursor = 'pointer';
                badge.style.transition = 'opacity 0.2s';
                badge.innerHTML = `${genre.name} <i class="bi bi-x-circle ms-2"></i>`;
                
                badge.addEventListener('mouseover', () => { badge.style.opacity = '0.8'; badge.style.textDecoration = 'line-through'; });
                badge.addEventListener('mouseout', () => { badge.style.opacity = '1'; badge.style.textDecoration = 'none'; });
                badge.onclick = () => removeGenre(genre.id);
                activeContainer.appendChild(badge);

                // Create Hidden Input
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'genres[]';
                hiddenInput.value = genre.id;
                hiddenContainer.appendChild(hiddenInput);
            });

            filterDropdown(); 
        }

        function filterDropdown() {
            const query = searchInput.value.trim().toLowerCase();
            dropdown.innerHTML = '';
            let exactMatchFound = false;

            if (query) {
                dropdown.classList.remove('d-none');
                
                const availableGenres = allGenres.filter(g => 
                    !selectedGenres.some(sg => sg.name.toLowerCase() === g.name.toLowerCase()) && 
                    g.name.toLowerCase().includes(query)
                );

                availableGenres.forEach(genre => {
                    if (genre.name.toLowerCase() === query) exactMatchFound = true;
                    createDropdownItem(genre.name, () => addGenre(genre.id, genre.name));
                });

                const isAlreadySelected = selectedGenres.some(sg => sg.name.toLowerCase() === query);
                if (!exactMatchFound && !isAlreadySelected) {
                    const newName = searchInput.value.trim();
                    createDropdownItem(`Add new: "${newName}"`, () => addGenre(newName, newName), 'text-primary fw-bold');
                }
            } else {
                dropdown.classList.add('d-none');
            }
        }

        function createDropdownItem(text, onClick, extraClasses = '') {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `list-group-item list-group-item-action ${extraClasses}`;
            btn.textContent = text;
            btn.onclick = onClick;
            dropdown.appendChild(btn);
        }

        function addGenre(id, name) {
            if (!selectedGenres.some(g => g.name.toLowerCase() === name.toLowerCase())) {
                selectedGenres.push({ id, name });
                searchInput.value = '';
                render();
                searchInput.focus();
            }
        }

        function removeGenre(id) {
            selectedGenres = selectedGenres.filter(g => g.id !== id);
            render();
        }

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (dropdown.children.length > 0) {
                    dropdown.children[0].click();
                }
            }
        });

        searchInput.addEventListener('input', filterDropdown);
        searchInput.addEventListener('focus', filterDropdown);

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('d-none');
            }
        });

        render();
    });
</script>
