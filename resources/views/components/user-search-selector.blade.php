@props([
    'name' => 'users[]',
    'initialUsers' => [],
    'label' => __('Select Users'),
    'withRole' => false,
    'roleOptions' => ['Developer', 'Publisher', 'Designer', 'Artist', 'Composer', 'Writer', 'Director']
])

@php
    // Map initial users to safe array, including pivot role if available
    $mappedUsers = collect($initialUsers)->map(function($user) use ($withRole) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'role' => ($withRole && $user->pivot) ? $user->pivot->role : null
        ];
    })->values()->toJson();
@endphp

<div class="user-search-selector mb-4" 
     data-name="{{ $name }}" 
     data-initial="{{ $mappedUsers }}"
     data-with-role="{{ $withRole ? '1' : '0' }}"
     data-role-options="{{ json_encode($roleOptions) }}">
     
    <label class="form-label fw-bold">{{ $label }}</label>
    
    <div class="selected-users d-flex flex-wrap mb-2"></div>
    
    <div class="position-relative">
        <div class="input-group">
            <select class="form-select filter-select" style="max-width: 140px; background-color: #f8f9fa;">
                <option value="all">Everyone</option>
                <option value="followers">Followers</option>
                <option value="following">Following</option>
                <option value="mutuals">Mutuals</option>
            </select>
            <input type="text" class="form-control search-input" placeholder="Search for users..." autocomplete="off">
        </div>
        
        <div class="list-group search-results d-none position-absolute w-100 shadow-sm mt-1" style="z-index: 1000; max-height: 250px; overflow-y: auto;">
        </div>
    </div>
    
    <div class="hidden-inputs"></div>
</div>

@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.19.0/js/md5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const getAvatarHtml = (user, size = 30) => {
            if (user.avatar_url) {
                return `<img src="${user.avatar_url}" class="rounded-circle border border-white" style="width: ${size}px; height: ${size}px; object-fit: cover;">`;
            } else {
                const hash = md5(user.username + '-avatar');
                const color = '#' + hash.substring(0, 6);
                const initial = user.username.charAt(0).toUpperCase();
                const fontSize = Math.max(12, Math.round(size * 0.45));
                return `<div class="rounded-circle text-white d-flex align-items-center justify-content-center border border-white" style="width: ${size}px; height: ${size}px; font-size: ${fontSize}px; background-color: ${color};">${initial}</div>`;
            }
        };

        document.querySelectorAll('.user-search-selector').forEach(container => {
            const selectedUsersContainer = container.querySelector('.selected-users');
            const hiddenInputsContainer = container.querySelector('.hidden-inputs');
            const searchInput = container.querySelector('.search-input');
            const filterSelect = container.querySelector('.filter-select');
            const resultsContainer = container.querySelector('.search-results');
            
            const inputName = container.getAttribute('data-name') || 'users[]';
            const baseName = inputName.replace('[]', '');
            const withRole = container.getAttribute('data-with-role') === '1';
            const roleOptions = JSON.parse(container.getAttribute('data-role-options') || '[]');
            
            let selectedUsers = [];
            try {
                selectedUsers = JSON.parse(container.getAttribute('data-initial')) || [];
            } catch(e) {
                selectedUsers = [];
            }

            const renderSelected = () => {
                selectedUsersContainer.innerHTML = '';
                hiddenInputsContainer.innerHTML = '';
                
                selectedUsers.forEach(user => {
                    if (withRole && !user.role) user.role = roleOptions[0] || 'Developer';

                    const badge = document.createElement('div');
                    const avatarHtml = getAvatarHtml(user, 24);

                    if (withRole) {
                        badge.className = 'd-inline-flex align-items-center bg-white border rounded-pill shadow-sm pe-2 mb-2 me-2';
                        
                        // Badge Left: User Info
                        const userSection = document.createElement('div');
                        userSection.className = 'd-flex align-items-center px-2 py-1 bg-primary text-white rounded-pill';
                        userSection.innerHTML = `<span class="me-2 d-flex">${avatarHtml}</span> <span class="fw-semibold me-2">${user.username}</span>`;
                        
                        // --- NEW TEXT + DATALIST LOGIC ---
                        
                        // 1. Generate the datalist options once per component instance
                        const dataListId = `roles-datalist-${baseName}`;
                        if (!document.getElementById(dataListId)) {
                            const dl = document.createElement('datalist');
                            dl.id = dataListId;
                            roleOptions.forEach(role => {
                                const opt = document.createElement('option');
                                opt.value = role;
                                dl.appendChild(opt);
                            });
                            container.appendChild(dl);
                        }

                        // 2. Create the free-text input and attach the datalist to it
                        const roleInput = document.createElement('input');
                        roleInput.type = 'text';
                        roleInput.className = 'form-control form-control-sm border-0 bg-transparent ms-1 py-0 shadow-none fw-bold text-secondary';
                        roleInput.value = user.role;
                        roleInput.setAttribute('list', dataListId); // Links to suggestions
                        roleInput.placeholder = "Role...";
                        
                        // Dynamically resize input based on text length to keep the pill looking clean
                        const calculateWidth = (val) => Math.max(70, val.length * 9) + 'px';
                        roleInput.style.width = calculateWidth(user.role);
                        
                        roleInput.oninput = (e) => {
                            user.role = e.target.value;
                            e.target.style.width = calculateWidth(e.target.value);
                            
                            const hidden = hiddenInputsContainer.querySelector(`input[data-user-id="${user.id}"]`);
                            if (hidden) hidden.value = e.target.value;
                        };
                        
                        // Badge Right: Remove Button
                        const removeIcon = document.createElement('i');
                        removeIcon.className = 'bi bi-x-circle-fill text-danger ms-2';
                        removeIcon.style.cursor = 'pointer';
                        removeIcon.onclick = () => {
                            selectedUsers = selectedUsers.filter(u => u.id !== user.id);
                            renderSelected();
                        };

                        badge.appendChild(userSection);
                        badge.appendChild(roleInput); // Appending text input instead of select
                        badge.appendChild(removeIcon);

                        // Hidden Input Formatted for Pivot Sync
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = `${baseName}[${user.id}][role]`; // => credits[ID][role] = SelectedRole
                        hidden.value = user.role;
                        hidden.setAttribute('data-user-id', user.id);
                        hiddenInputsContainer.appendChild(hidden);

                    } else {
                        // Standard Layout (Playlists etc.)
                        badge.className = 'badge bg-primary text-white d-inline-flex align-items-center py-1 px-3 fs-6 rounded-pill cursor-pointer shadow-sm mb-2 me-2';
                        badge.style.cursor = 'pointer';
                        badge.innerHTML = `<span class="me-2 d-flex">${avatarHtml}</span> ${user.username} <i class="bi bi-x-circle ms-2"></i>`;
                        badge.onclick = () => {
                            selectedUsers = selectedUsers.filter(u => u.id !== user.id);
                            renderSelected();
                        };

                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = inputName;
                        hidden.value = user.id;
                        hiddenInputsContainer.appendChild(hidden);
                    }
                    
                    selectedUsersContainer.appendChild(badge);
                });
            };

            let debounceTimer;
            const fetchUsers = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const query = searchInput.value.trim();
                    const filter = filterSelect.value;
                    
                    if (query.length < 1 && filter === 'all') {
                        resultsContainer.classList.add('d-none');
                        return;
                    }

                    fetch(`/api/users/search?q=${encodeURIComponent(query)}&filter=${filter}`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data.length === 0) {
                            resultsContainer.innerHTML = '<div class="list-group-item text-muted">No users found.</div>';
                        } else {
                            data.forEach(user => {
                                if (selectedUsers.some(u => u.id === user.id)) return;
                                
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';
                                
                                const avatarHtml = getAvatarHtml(user, 35);
                                
                                btn.innerHTML = `${avatarHtml} <span class="fw-bold">${user.username}</span>`;
                                btn.onclick = () => {
                                    if (withRole) user.role = roleOptions[0] || 'Developer';
                                    selectedUsers.push(user);
                                    renderSelected();
                                    searchInput.value = '';
                                    resultsContainer.classList.add('d-none');
                                };
                                resultsContainer.appendChild(btn);
                            });
                            if (resultsContainer.innerHTML === '') {
                                resultsContainer.innerHTML = '<div class="list-group-item text-muted">All matching users already selected.</div>';
                            }
                        }
                        resultsContainer.classList.remove('d-none');
                    });
                }, 300);
            };

            searchInput.addEventListener('input', fetchUsers);
            filterSelect.addEventListener('change', fetchUsers);
            searchInput.addEventListener('focus', fetchUsers);

            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    resultsContainer.classList.add('d-none');
                }
            });

            renderSelected();
        });
    });
</script>
@endonce