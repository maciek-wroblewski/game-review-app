import md5 from 'blueimp-md5';

class SelectorInstance {
    constructor(container) {
        this.container = container;
        this.selectedUsersContainer = container.querySelector('.selected-users');
        this.hiddenInputsContainer = container.querySelector('.hidden-inputs');
        this.searchInput = container.querySelector('.search-input');
        this.filterSelect = container.querySelector('.filter-select');
        this.resultsContainer = container.querySelector('.search-results');
        
        this.inputName = container.getAttribute('data-name') || 'users[]';
        this.baseName = this.inputName.replace('[]', '');
        this.withRole = container.getAttribute('data-with-role') === '1';
        this.roleOptions = JSON.parse(container.getAttribute('data-role-options') || '[]');
        
        this.selectedUsers = [];
        try {
            this.selectedUsers = JSON.parse(container.getAttribute('data-initial')) || [];
        } catch (e) {
            this.selectedUsers = [];
        }

        this.debounceTimer = null;
        this.init();
    }

    init() {
        this.searchInput.addEventListener('input', () => this.fetchUsers());
        this.filterSelect.addEventListener('change', () => this.fetchUsers());
        this.searchInput.addEventListener('focus', () => this.fetchUsers());

        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.resultsContainer.classList.add('d-none');
            }
        });

        this.renderSelected();
    }

    getAvatarHtml(user, size = 30) {
        if (user.avatar_url) {
            return `<img src="${user.avatar_url}" class="rounded-circle border border-white" style="width: ${size}px; height: ${size}px; object-fit: cover;">`;
        }
        
        const hash = md5(user.username + '-avatar');
        const color = '#' + hash.substring(0, 6);
        const initial = user.username.charAt(0).toUpperCase();
        const fontSize = Math.max(12, Math.round(size * 0.45));
        return `<div class="rounded-circle text-white d-flex align-items-center justify-content-center border border-white" style="width: ${size}px; height: ${size}px; font-size: ${fontSize}px; background-color: ${color};">${initial}</div>`;
    }

    renderSelected() {
        this.selectedUsersContainer.innerHTML = '';
        this.hiddenInputsContainer.innerHTML = '';
        
        this.selectedUsers.forEach(user => {
            if (this.withRole && !user.role) {
                user.role = this.roleOptions[0] || 'Developer';
            }

            const badge = document.createElement('div');
            const avatarHtml = this.getAvatarHtml(user, 24);

            if (this.withRole) {
                badge.className = 'd-inline-flex align-items-center bg-white border rounded-pill shadow-sm pe-2 mb-2 me-2';
                
                const userSection = document.createElement('div');
                userSection.className = 'd-flex align-items-center px-2 py-1 bg-primary text-white rounded-pill';
                userSection.innerHTML = `<span class="me-2 d-flex">${avatarHtml}</span> <span class="fw-semibold me-2">${user.username}</span>`;
                
                const dataListId = `roles-datalist-${this.baseName}`;
                if (!document.getElementById(dataListId)) {
                    const dl = document.createElement('datalist');
                    dl.id = dataListId;
                    this.roleOptions.forEach(role => {
                        const opt = document.createElement('option');
                        opt.value = role;
                        dl.appendChild(opt);
                    });
                    this.container.appendChild(dl);
                }

                const roleInput = document.createElement('input');
                roleInput.type = 'text';
                roleInput.className = 'form-control form-control-sm border-0 bg-transparent ms-1 py-0 shadow-none fw-bold text-secondary';
                roleInput.value = user.role;
                roleInput.setAttribute('list', dataListId);
                roleInput.placeholder = "Role...";
                
                const calculateWidth = (val) => Math.max(70, val.length * 9) + 'px';
                roleInput.style.width = calculateWidth(user.role);
                
                roleInput.oninput = (e) => {
                    user.role = e.target.value;
                    e.target.style.width = calculateWidth(e.target.value);
                    
                    const hidden = this.hiddenInputsContainer.querySelector(`input[data-user-id="${user.id}"]`);
                    if (hidden) hidden.value = e.target.value;
                };
                
                const removeIcon = document.createElement('i');
                removeIcon.className = 'bi bi-x-circle-fill text-danger ms-2';
                removeIcon.style.cursor = 'pointer';
                removeIcon.onclick = () => {
                    this.selectedUsers = this.selectedUsers.filter(u => u.id !== user.id);
                    this.renderSelected();
                };

                badge.appendChild(userSection);
                badge.appendChild(roleInput);
                badge.appendChild(removeIcon);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `${this.baseName}[${user.id}][role]`;
                hidden.value = user.role;
                hidden.setAttribute('data-user-id', user.id);
                this.hiddenInputsContainer.appendChild(hidden);

            } else {
                badge.className = 'badge bg-primary text-white d-inline-flex align-items-center py-1 px-3 fs-6 rounded-pill cursor-pointer shadow-sm mb-2 me-2';
                badge.style.cursor = 'pointer';
                badge.innerHTML = `<span class="me-2 d-flex">${avatarHtml}</span> ${user.username} <i class="bi bi-x-circle ms-2"></i>`;
                badge.onclick = () => {
                    this.selectedUsers = this.selectedUsers.filter(u => u.id !== user.id);
                    this.renderSelected();
                };

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = this.inputName;
                hidden.value = user.id;
                this.hiddenInputsContainer.appendChild(hidden);
            }
            
            this.selectedUsersContainer.appendChild(badge);
        });
    }

    fetchUsers() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            const query = this.searchInput.value.trim();
            const filter = this.filterSelect.value;
            
            if (query.length < 1 && filter === 'all') {
                this.resultsContainer.classList.add('d-none');
                return;
            }

            fetch(`/api/users/search?q=${encodeURIComponent(query)}&filter=${filter}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.resultsContainer.innerHTML = '';
                if (data.length === 0) {
                    this.resultsContainer.innerHTML = '<div class="list-group-item text-muted">No users found.</div>';
                } else {
                    data.forEach(user => {
                        if (this.selectedUsers.some(u => u.id === user.id)) return;
                        
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';
                        
                        const avatarHtml = this.getAvatarHtml(user, 35);
                        
                        btn.innerHTML = `${avatarHtml} <span class="fw-bold">${user.username}</span>`;
                        btn.onclick = () => {
                            if (this.withRole) user.role = this.roleOptions[0] || 'Developer';
                            this.selectedUsers.push(user);
                            this.renderSelected();
                            this.searchInput.value = '';
                            this.resultsContainer.classList.add('d-none');
                        };
                        this.resultsContainer.appendChild(btn);
                    });
                    if (this.resultsContainer.innerHTML === '') {
                        this.resultsContainer.innerHTML = '<div class="list-group-item text-muted">All matching users already selected.</div>';
                    }
                }
                this.resultsContainer.classList.remove('d-none');
            });
        }, 300);
    }
}

export default class UserSearchSelector {
    constructor() {
        this.initSelectors();
        document.addEventListener('DOMContentLoaded', this.initSelectors.bind(this));
    }

    initSelectors() {
        document.querySelectorAll('.user-search-selector:not([data-initialized])').forEach(container => {
            container.setAttribute('data-initialized', 'true');
            new SelectorInstance(container);
        });
    }
}

