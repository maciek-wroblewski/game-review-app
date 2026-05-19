@props(['post'])

@php

$isReview =
    method_exists($post, 'isReview') &&
    $post->isReview() &&
    $post->review;

$canModerate =
    auth()->check() &&
    (
        auth()->id() === $post->user_id ||
        auth()->user()->is_admin
    );

$isAdmin =
    auth()->check() &&
    auth()->user()->is_admin;

@endphp

<style>

.js-post-card {

    transition:
        transform .25s cubic-bezier(.4,0,.2,1),
        box-shadow .25s cubic-bezier(.4,0,.2,1);

    will-change:
        transform,
        box-shadow;
}

.js-post-card:hover {

    transform:
        scale(1.012)
        translateY(-2px);

    box-shadow:
        0 .65rem 1.25rem rgba(0,0,0,.08)!important;

}

.admin-moderation-outline {

    border:
        1px solid rgba(220,53,69,.25)!important;

    box-shadow:

        0 0 0 1px rgba(220,53,69,.06),

        0 0 18px rgba(220,53,69,.08)!important;
}

.admin-toolbar {

    background:

        linear-gradient(
            90deg,
            rgba(220,53,69,.08),
            rgba(220,53,69,.02)
        );

}

.admin-tool-btn {

    min-width:115px;
}

.pinned-banner {

    background:

        linear-gradient(
            90deg,
            rgba(255,193,7,.18),
            rgba(255,193,7,.05)
        );

}

.featured-banner {

    background:

        linear-gradient(
            90deg,
            rgba(13,110,253,.18),
            rgba(13,110,253,.05)
        );

}

</style>

<div
class="js-post-wrapper
js-post-card
card
shadow-sm
mb-4
border-0
overflow-hidden

{{ $isReview
? 'd-flex flex-row align-items-stretch'
: '' }}

{{ $isAdmin
? 'admin-moderation-outline'
: '' }}"
data-post-id="{{ $post->id }}">

@if($isReview && $post->review->type === 'recommendation')

<x-post.rating-meter
:rating="$post->review->rating ?? 0" />

@endif

<div
class="flex-grow-1 d-flex flex-column
{{ $isReview ? 'bg-white':'' }}">

@if($post->trashed())

<p
class="card-text
text-muted
fst-italic
p-3">

[This post has been deleted]

</p>

@else

{{-- PINNED --}}

@if($post->is_pinned)

<div
class="pinned-banner
px-3
py-2
border-bottom">

<i
class="bi bi-pin-angle-fill
text-warning
me-1"></i>

<span
class="fw-semibold">

Pinned Post

</span>

</div>

@endif

{{-- FEATURED --}}

@if($post->featured ?? false)

<div
class="featured-banner
px-3
py-2
border-bottom">

<i
class="bi bi-stars
text-primary
me-1"></i>

<span
class="fw-semibold">

Featured Review

</span>

</div>

@endif

{{-- SUSPENDED AUTHOR --}}

@if($post->user?->is_suspended)

<div
class="alert
alert-danger
rounded-0
border-0
mb-0">

<i
class="bi bi-slash-circle-fill me-1"></i>

Author suspended

</div>

@endif

{{-- ADMIN BAR --}}

@if($isAdmin)

<div
class="admin-toolbar
px-3
py-2
border-bottom
d-flex
justify-content-between
align-items-center">

<div
class="small
fw-bold
text-danger">

<i
class="bi bi-shield-lock-fill me-1"></i>

ADMIN MODE

</div>

<div>

<span
class="badge bg-danger">

#{{ $post->id }}

</span>

</div>

</div>

@endif

@if($post->is_locked)

<div
class="alert
alert-secondary
rounded-0
border-0
mb-0">

<i
class="bi bi-lock-fill me-2"></i>

This post is locked.

Replies disabled.

</div>

@endif

<div
class="edit_form_collapsable">

<x-post.content
:post="$post" />

<x-post.footer
:post="$post" />

@if($canModerate)

<div
class="px-3
pb-3
d-flex
gap-2
flex-wrap">

{{-- DELETE --}}

<form
method="POST"
action="/posts/{{ $post->id }}"
onsubmit="return confirm('Delete post?')">

@csrf
@method('DELETE')

<button
class="btn
btn-sm
btn-outline-danger
admin-tool-btn">

<i
class="bi bi-trash-fill me-1"></i>

Delete

</button>

</form>

{{-- PIN --}}

@if($isAdmin)

<form
method="POST"
action="/admin/posts/{{ $post->id }}/pin">

@csrf

<button
class="btn
btn-sm

{{ $post->is_pinned
? 'btn-warning'
: 'btn-outline-warning' }}

admin-tool-btn">

<i
class="bi bi-pin-angle-fill me-1"></i>

{{ $post->is_pinned
? 'Unpin'
: 'Pin' }}

</button>

</form>

@endif

@if($isAdmin)

<form
method="POST"
action="/admin/posts/{{ $post->id }}/lock">

@csrf

<button
class="btn
btn-sm

{{ $post->is_locked
? 'btn-secondary'
: 'btn-outline-secondary' }}

admin-tool-btn">

<i
class="bi
{{ $post->is_locked
? 'bi-unlock-fill'
: 'bi-lock-fill' }}
me-1"></i>

{{ $post->is_locked
? 'Unlock'
: 'Lock' }}

</button>

</form>

@endif

</div>

@endif

</div>

<x-post.edit-form
:post="$post" />

@endif

<x-post.reply-container
:post="$post">

@if(!$post->is_locked)

<x-post.comment-create
:hubType="$post->hubable_type ?? $post->hub_type"
:hubId="$post->hubable_id ?? $post->hub_id"
:parentId="$post->id" />

@endif

</x-post.reply-container>

<x-post.replies-container
:postId="$post->id"
id="accordion-{{ $post->id }}">

<x-post.replies-list : />

</x-post.replies-container>

</div>

</div>