@props([
    'users',
    'feedId' => 'users-grid',
    'layout' => 'compact',
    'loadMoreText' => __('common.load_more_users')
])

<div class="user-list-wrapper">
    <div id="{{ $feedId }}" class="row g-4 mb-4">
        @foreach($users as $user)
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                <x-user.card :user="$user" :layout="$layout" />
            </div>
        @endforeach
    </div>

    @if($users->hasMorePages())
        <div class="mt-2">
            <x-load-more 
                :paginator="$users" 
                target="#{{ $feedId }}" 
                :text="$loadMoreText" 
            />
        </div>
    @endif
</div>
