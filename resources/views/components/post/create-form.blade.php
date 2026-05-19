@props(['hubType' => null, 'hubId' => null, 'parentId' => null, 'reviewType' => null])

@php
    $isRecommendation = $reviewType === 'recommendation';
    $uid = \Illuminate\Support\Str::random(8);
@endphp

<div class="animate-fade-in js-create-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isRecommendation ? 'd-flex flex-row align-items-stretch' : '' }}"
    data-hub-type="{{ $hubType }}"
    data-hub-id="{{ $hubId }}"
    data-parent-id="{{ $parentId }}"
    data-review-type="{{ $reviewType }}">

    @if($isRecommendation)
        <x-post.rating-meter :rating="10" editable="true" />
    @endif

    <div class="flex-grow-1 d-flex flex-column p-3 bg-white" style="min-width: 0;">
        <div class="mb-3">
            <label class="form-label fw-bold text-muted small">Write a post...</label>
            <textarea class="js-create-textarea form-control" rows="4" placeholder="What's on your mind?"></textarea>
        </div>

        <div class="mb-3 border-bottom pb-3">
            <label class="form-label fw-bold text-muted small">Media</label>
            <x-media-upload 
                multiple="true" 
                inputName="create_media_ids[]" 
                accept="image/*,video/mp4" 
                previewClass="rounded border bg-dark"
                previewStyle="height: 80px; width: 80px; object-fit: cover;"
            />
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
            <x-post.form-toggles 
                :spoilerClass="'js-create-spoiler'" 
                :lockClass="'js-create-locked'"
                :spoilerId="'createSpoiler-' . $uid"
                :lockId="'createLocked-' . $uid"
            />
            
            <x-post.form-actions 
                :clearClass="'js-btn-create-clear'"
                :submitClass="'js-btn-submit-post'"
                :spinnerClass="'js-submit-spinner'"
            />
        </div>
    </div>
</div>
