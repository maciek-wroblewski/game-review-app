@php
    // Helper variable to keep the code extremely clean
    $isFollowers = $type === 'followers';
@endphp

<x-layout headtitle="{{ $user->username }} {{ $isFollowers ? 'Followers' : 'Following' }}">
    <div class="container py-5">
        
        {{-- Header Section --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="fw-bold mb-1">
                    {{ $isFollowers ? __('users.followers_title') : __('users.following_title') }}
                </h1>
                <p class="text-muted mb-0">
                    {{ $isFollowers ? __('users.followers_desc', ['username' => $user->username]) : __('users.following_desc', ['username' => $user->username]) }}
                </p>
            </div>
            <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> {{ __('common.back_to_profile') }}
            </a>
        </div>

        {{-- Bootstrap Tabs for Easy Navigation --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $isFollowers ? 'active fw-bold' : 'text-muted' }}" 
                   href="{{ url('/users/'.$user->id.'/followers') }}">
                    {{ __('users.followers_title') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ! $isFollowers ? 'active fw-bold' : 'text-muted' }}" 
                   href="{{ url('/users/'.$user->id.'/following') }}">
                    {{ __('users.following_title') }}
                </a>
            </li>
        </ul>

        {{-- Content Section --}}
        @if($connections->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">
                    {{ $isFollowers ? __('users.no_followers_yet') : __('users.not_following_yet') }}
                </h4>
                <p class="mb-0">
                    {{ $isFollowers ? __('users.no_followers_detail') : __('users.not_following_detail') }}
                </p>
            </div>
        @else
            {{-- Dynamic ID for the Load More target --}}
            <div id="{{ $type }}-grid-wrapper" class="row g-4">
                @foreach($connections as $connectionUser)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                        {{-- Render component directly - bypassing the junk wrapper file! --}}
                        <x-user.card :user="$connectionUser" layout="compact" />
                    </div>
                @endforeach
            </div>

            <x-load-more :paginator="$connections" target="#{{ $type }}-grid-wrapper" />
        @endif
    </div>
</x-layout>