@props(['hubType' => null, 'hubId' => null, 'parentId' => null, 'reviewType' => null])

@php
    $isRecommendation = $reviewType === 'recommendation';
    $uid = \Illuminate\Support\Str::random(8);
@endphp

@auth
    {{-- Added position-relative to the wrapper --}}
    <div class="position-relative animate-fade-in js-create-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isRecommendation ? 'd-flex flex-row align-items-stretch' : '' }}"
        data-hub-type="{{ $hubType }}"
        data-hub-id="{{ $hubId }}"
        data-parent-id="{{ $parentId }}"
        data-review-type="{{ $reviewType }}">

        {{-- Suspended Overlay --}}
        @if(auth()->user()->is_suspended)
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center z-3" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(3px);">
                <div class="alert alert-danger border-0 m-0 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>{{ __('common.account_suspended') }}</strong>
                    </div>
                </div>
            </div>
        @endif
        
        @if($isRecommendation)
            <x-post.rating-meter :rating="10" editable="true" />
        @endif

        <div class="flex-grow-1 d-flex flex-column p-3 bg-white" style="min-width: 0;">
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">{{ __('posts.write_post') }}</label>
                <textarea class="js-create-textarea form-control" rows="4" placeholder="{{ __('posts.what_on_mind') }}"></textarea>
            </div>

            <div class="mb-3 border-bottom pb-3">
                <label class="form-label fw-bold text-muted small">{{ __('posts.media') }}</label>
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
@else
    {{-- Guest Call to Action --}}
    <div class="card shadow-sm mb-4 border-0 bg-light text-center p-4">
        <p class="text-muted mb-0">
            <a href="{{ route('login') }}" class="text-decoration-none fw-bold">{{ __('common.login') }}</a> or <a href="{{ route('register') }}" class="text-decoration-none fw-bold">{{ __('common.register') }}</a> to write a post.
        </p>
    </div>
@endauth