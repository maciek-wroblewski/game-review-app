@props(['post', 'parentIsSpoiler' => false])

@if($post)

@php
$height = $post->media->count() > 0 ? '1' : '3';
@endphp

<x-clickable-card :link="'/posts/' . $post->id">

    <div class="border rounded p-3 hover-bg-light" style="background-color: #f8f9fa; cursor: pointer;"
        data-href="/posts/{{ $post->id }}">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-2">

            <div class="d-flex align-items-center gap-2">

                <x-user.avatar :user="$post->author" layout="compact" :size="'24px'" />

                <div>

                    <div class="fw-semibold small">

                        @if($post->author->trashed())
                            {{ __('users.deleted_user') }}
                        @else
                            {{ $post->author->username ?? 'Deleted User' }}
                        @endif
                        <span class="text-muted small" style="font-size: 0.75rem;">{{ $post->created_at->diffForHumans() }}</span>

                    </div>

                </div>

            </div>

        </div>

        <!-- Content -->
        <div>
            <x-post.spoiler :is-spoiler="$post->is_spoiler">
                <x-truncate-text :size="$post->media->count() > 0 ? 1 : 2">
                    <x-post.text-body :body="$post->body" />
                </x-truncate-text>
                <x-post.media-grid :media="$post->media" />
            </x-post.spoiler>
        </div>

    </div>

</x-clickable-card>

@else

<div class="border rounded p-3 text-muted small bg-light">

    This quoted post has been deleted.

</div>

@endif