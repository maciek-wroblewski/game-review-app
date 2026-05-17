@props([
    'hubType' => null,
    'hubId' => null,
    'parentId' => null
])

@php
    $uid = \Illuminate\Support\Str::random(8);
@endphp

<div class="js-create-comment-card card shadow-sm mb-3 border-0 overflow-hidden bg-white"
    data-hub-type="{{ $hubType }}"
    data-hub-id="{{ $hubId }}"
    data-parent-id="{{ $parentId }}">

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
