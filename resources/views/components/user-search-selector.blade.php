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
