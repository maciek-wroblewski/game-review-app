@php
    $isPosts = $type === 'posts';
@endphp

<x-layout headtitle="{{ $user->username }} {{ $isPosts ? 'Posts' : 'Reviews' }}">
    <div class="container py-5">
        
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">
                    {{ $isPosts ? __('users.posts_title') : __('users.reviews_title') }}
                </h1>
                <p class="text-muted mb-0">
                    {{ $isPosts ? __('users.posts_desc', ['username' => $user->username]) : __('users.reviews_desc', ['username' => $user->username]) }}
                </p>
            </div>
            <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('common.back_to_profile') }}
            </a>
        </div>

        {{-- Bootstrap Tabs for Easy Navigation --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $isPosts ? 'active fw-bold' : 'text-muted' }}" 
                   href="{{ url('/users/'.$user->id.'/posts') }}">
                    {{ __('users.posts_title') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ! $isPosts ? 'active fw-bold' : 'text-muted' }}" 
                   href="{{ url('/users/'.$user->id.'/reviews') }}">
                    {{ __('users.reviews_title') }}
                </a>
            </li>
        </ul>

        {{-- Content Section --}}
        @if($posts->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">
                    {{ $isPosts ? __('users.no_posts_yet') : __('users.no_reviews_yet') }}
                </h4>
                <p class="mb-0">
                    {{ $isPosts ? __('users.no_community_posts') : __('users.no_reviews_detail') }}
                </p>
            </div>
        @else
            {{-- Your dynamic component handles the rest! --}}
            <x-post.list :posts="$posts" />
        @endif

    </div>
</x-layout>