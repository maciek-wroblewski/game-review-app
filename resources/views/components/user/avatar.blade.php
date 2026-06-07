@props(['user', 'size' => null])

{{-- 1. Static Trigger Wrapper: A solid block to provide an uninterrupted hover zone --}}
<div class="user-popover-wrapper position-relative d-inline-block align-middle">
    
    <span 
       class="user-card-trigger d-block text-decoration-none" 
       data-bs-toggle="popover" 
       data-bs-trigger="manual">
       
        {{-- Base Avatar --}}
        <x-user.static-avatar :user="$user" :size="$size" />
        
    </span>

    {{-- Popover Template Cache --}}
    <div class="popover-template d-none">
        <x-user.card :user="$user" layout="compact" />
    </div>
    
</div>
