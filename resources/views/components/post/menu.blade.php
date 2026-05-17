@props(['post'])

@auth
@if(auth()->id() === $post->user_id && !$post->trashed())
<div class="dropdown custom-post-dropdown">
    <!-- The trigger button remains a standard, layout-stable inline element -->
    <button class="btn btn-light btn-sm rounded-circle border-0 dropdown-trigger" data-bs-toggle="dropdown"
    aria-expanded="false">
        <i class="bi bi-three-dots"></i>
    </button>

    <!-- We let Bootstrap handle the display state and absolute positioning on the UL container -->
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0 standard-dropdown-flow" style="z-index: 5;">
        <div class="dropdown-animate-container p-1">
            <li>
                <!-- This button triggers the edit-form component -->
                <button class="js-btn-edit dropdown-item py-2 px-3" data-post-id="{{ $post->id }}">
                    <i class="bi bi-pencil me-2"></i><span>Edit</span>
                </button>
            </li>
            <li>
                <!-- REPLACED BUTTON WITH FORM FOR PLAIN RELOAD DELETE -->
                <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger py-2 px-3 w-100 text-start" onclick="return confirm('Permanently delete this post?');">
                        <i class="bi bi-trash me-2"></i><span>Delete</span>
                    </button>
                </form>
            </li>
        </div>
    </ul>
</div>
@endif
@endauth

@once
<style>
/* --- Stable Trigger Layout --- */
.custom-post-dropdown {
    display: inline-block;
    line-height: 1;
}

.custom-post-dropdown .dropdown-trigger {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s ease;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.custom-post-dropdown .dropdown-trigger:hover {
    background-color: #e9ecef !important;
    transform: scale(1.1) rotate(90deg);
    cursor: pointer;
}

.custom-post-dropdown .dropdown-trigger:active {
    transform: scale(0.95) rotate(90deg);
}

/* --- Decoupled Animation Engine --- */
.custom-post-dropdown .standard-dropdown-flow {
    background: transparent !important;
}

.custom-post-dropdown .dropdown-animate-container {
    background-color: #fff;
    border-radius: 10px;
    min-width: 140px;
    opacity: 0;
    transform: scale(0.95) translateY(-5px);
    transform-origin: top right;
    transition: opacity 0.15s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-post-dropdown .standard-dropdown-flow.show .dropdown-animate-container {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* --- Dropdown Item Styles --- */
.custom-post-dropdown .dropdown-item {
    border-radius: 6px;
    transition: background-color 0.15s ease, color 0.15s ease;
    display: flex;
    align-items: center;
    cursor: pointer;
    /* Ensure form button fills the width like the original button */
    width: 100%; 
}

.custom-post-dropdown .dropdown-item:not(.text-danger):hover {
    background-color: #f1f3f5;
    color: #212529;
}

.custom-post-dropdown .dropdown-item.text-danger:hover {
    background-color: #fff5f5;
    color: #e03131 !important;
}

.custom-post-dropdown .dropdown-item i,
.custom-post-dropdown .dropdown-item span {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-post-dropdown .dropdown-item:hover i {
    transform: scale(1.15);
}

.custom-post-dropdown .dropdown-item:hover span {
    transform: translateX(3px);
}

.custom-post-dropdown .dropdown-item:active {
    transform: scale(0.98);
}
</style>
<script>
document.addEventListener('click', async (e) => {
    // 1. Handle Edit Trigger
    // Note: Delete logic is now handled by the standard form submission above, 
    // so we removed the delete AJAX handler from this script.
    
    const editBtn = e.target.closest('.js-btn-edit');
    if (editBtn) {
        // Find the shared parent wrapper
        const card = editBtn.closest('.js-post-card');
        if (!card) return;

        // Find the edit container inside this specific card
        const editContainer = card.querySelector('.js-edit-container');
        if (!editContainer) return;

        // Dispatch the custom event to the container
        editContainer.dispatchEvent(new CustomEvent('toggle-edit', { 
            bubbles: true,
            detail: { card } // Pass the card along so the receiver can find the view container
        }));
    }
});
</script>
@endonce
