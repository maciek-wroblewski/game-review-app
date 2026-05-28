@props([
    'hubType' => null,
    'hubId' => null,
    'parentId' => null
])

@php
    $uid = \Illuminate\Support\Str::random(8);
@endphp

@auth
    {{-- Added position-relative --}}
    <div class="position-relative js-create-comment-card card shadow-sm mb-3 border-0 overflow-hidden bg-white"
        data-hub-type="{{ $hubType }}"
        data-hub-id="{{ $hubId }}"
        data-parent-id="{{ $parentId }}">

        {{-- Suspended Overlay --}}
        @if(auth()->user()->is_suspended)
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center z-3" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(3px);">
                <div class="alert alert-danger border-0 m-0 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Your account is currently suspended.</strong>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="p-3">
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">Reply as a comment...</label>
                <textarea class="js-comment-textarea form-control" rows="3" placeholder="Write your reply..."></textarea>
            </div>

            <div class="mb-3 border-bottom pb-3">
                <label class="form-label fw-bold text-muted small">Media</label>
                <x-media-upload 
                    multiple="true" 
                    inputName="comment_media_ids[]" 
                    accept="image/*,video/mp4" 
                    previewClass="rounded border bg-dark"
                    previewStyle="height: 80px; width: 80px; object-fit: cover;"
                />
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
                <x-post.form-toggles 
                    :spoilerClass="'js-comment-spoiler'" 
                    :lockClass="'js-comment-locked'"
                    :spoilerId="'commentSpoiler-' . $uid"
                    :lockId="'commentLocked-' . $uid"
                />
                
                <x-post.form-actions 
                    :clearClass="'js-btn-comment-clear'"
                    :submitClass="'js-btn-comment-submit'"
                    :spinnerClass="'js-comment-submit-spinner'"
                    :clearLabel="'Clear'"
                    :submitLabel="'Reply'"
                />
            </div>
        </div>
    </div>
@else
    {{-- Guest Call to Action --}}
    <div class="card shadow-sm mb-3 border-0 bg-light text-center p-3">
        <p class="text-muted mb-0 small">
            <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Log in</a> to leave a comment.
        </p>
    </div>
@endauth