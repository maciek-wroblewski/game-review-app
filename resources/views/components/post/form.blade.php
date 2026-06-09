@props([
    'post' => null,
    'hubType' => null,
    'hubId' => null,
    'parentId' => null,
    'reviewType' => null,
    'isComment' => false
])

@php
    $isEdit = !empty($post);
    
    if ($isEdit) {
        $isComment = !empty($post->parent_id);
        $hubType = $post->hub_type;
        $hubId = $post->hub_id;
        $parentId = $post->parent_id;
    } else {
        $isComment = $isComment || !empty($parentId);
    }
    
    $isRecommendation = !$isComment && ($isEdit ? ($post->isReview() && $post->review?->type === 'recommendation') : ($reviewType === 'recommendation'));
    $uid = $isEdit ? $post->id : \Illuminate\Support\Str::random(8);

    // Dynamic Form Properties
    if ($isEdit) {
        $textareaLabel = __('posts.post_edit');
        $textareaClass = 'js-edit-textarea';
        $textareaRows = 4;
        $textareaValue = $post->body;
        $textareaPlaceholder = '';
        
        $mediaLabel = __('posts.media');
        $mediaInputName = 'media_ids[]';
        $existingMedia = $post->media;
        
        $spoilerClass = 'js-edit-spoiler';
        $lockClass = 'js-edit-locked';
        $spoilerId = 'isSpoiler-' . $uid;
        $lockId = 'isLocked-' . $uid;
        $spoilerChecked = $post->is_spoiler;
        $lockChecked = $post->is_locked;
        
        $clearClass = 'js-btn-cancel';
        $submitClass = 'js-btn-save';
        $spinnerClass = 'js-save-spinner';
        $clearLabel = __('common.cancel');
        $submitLabel = __('common.save_changes');
    } else {
        $textareaLabel = $isComment ? __('posts.reply_as_comment') : __('posts.write_post');
        $textareaClass = $isComment ? 'js-comment-textarea' : 'js-create-textarea';
        $textareaRows = $isComment ? 3 : 4;
        $textareaValue = '';
        $textareaPlaceholder = $isComment ? __('posts.write_your_reply') : __('posts.what_on_mind');
        
        $mediaLabel = $isComment ? __('common.media') : __('posts.media');
        $mediaInputName = $isComment ? 'comment_media_ids[]' : 'create_media_ids[]';
        $existingMedia = [];
        
        $spoilerClass = $isComment ? 'js-comment-spoiler' : 'js-create-spoiler';
        $lockClass = $isComment ? 'js-comment-locked' : 'js-create-locked';
        $spoilerId = ($isComment ? 'commentSpoiler-' : 'createSpoiler-') . $uid;
        $lockId = ($isComment ? 'commentLocked-' : 'createLocked-') . $uid;
        $spoilerChecked = false;
        $lockChecked = false;
        
        $clearClass = $isComment ? 'js-btn-comment-clear' : 'js-btn-create-clear';
        $submitClass = $isComment ? 'js-btn-comment-submit' : 'js-btn-submit-post';
        $spinnerClass = $isComment ? 'js-comment-submit-spinner' : 'js-submit-spinner';
        $clearLabel = $isComment ? __('common.clear') : null;
        $submitLabel = $isComment ? __('common.reply') : null;
    }
@endphp

@if($isEdit)
    <div class="js-edit-container" data-open="false">
        <div class="js-edit-mode border-top border-light p-3 bg-light">
            <div class="flex-grow-1">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">{{ $textareaLabel }}</label>
                    <textarea class="{{ $textareaClass }} form-control" rows="{{ $textareaRows }}" placeholder="{{ $textareaPlaceholder }}">{{ $textareaValue }}</textarea>
                </div>

                <div class="mb-3 border-bottom pb-3">
                    <label class="form-label fw-bold text-muted small">{{ $mediaLabel }}</label>
                    <x-media-upload 
                        multiple="true" 
                        inputName="{{ $mediaInputName }}" 
                        accept="image/*,video/mp4" 
                        :existingMedia="$existingMedia"
                        previewClass="rounded border bg-dark"
                        previewStyle="height: 80px; width: 80px; object-fit: cover;"
                    />
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
                    <x-post.form-toggles 
                        :spoilerClass="$spoilerClass" 
                        :lockClass="$lockClass"
                        :spoilerId="$spoilerId"
                        :lockId="$lockId"
                        :spoilerChecked="$spoilerChecked"
                        :lockChecked="$lockChecked"
                    />
                    
                    <x-post.form-actions 
                        :clearClass="$clearClass"
                        :submitClass="$submitClass"
                        :spinnerClass="$spinnerClass"
                        :clearLabel="$clearLabel"
                        :submitLabel="$submitLabel"
                    />
                </div>
            </div>
        </div>
    </div>
@else
    @auth
        <div class="position-relative card shadow-sm border-0 overflow-hidden {{ $isComment ? 'js-create-comment-card mb-3 bg-white' : 'animate-fade-in js-create-post-card mb-4 ' . ($isRecommendation ? 'd-flex flex-row align-items-stretch' : '') }}"
            data-hub-type="{{ $hubType }}"
            data-hub-id="{{ $hubId }}"
            data-parent-id="{{ $parentId }}"
            @if(!$isComment) data-review-type="{{ $reviewType }}" @endif>

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

            <div class="{{ $isComment ? '' : 'flex-grow-1 d-flex flex-column' }} p-3 bg-white" style="min-width: 0;">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">{{ $textareaLabel }}</label>
                    <textarea class="{{ $textareaClass }} form-control" rows="{{ $textareaRows }}" placeholder="{{ $textareaPlaceholder }}">{{ $textareaValue }}</textarea>
                </div>

                <div class="mb-3 border-bottom pb-3">
                    <label class="form-label fw-bold text-muted small">{{ $mediaLabel }}</label>
                    <x-media-upload 
                        multiple="true" 
                        inputName="{{ $mediaInputName }}" 
                        accept="image/*,video/mp4" 
                        :existingMedia="$existingMedia"
                        previewClass="rounded border bg-dark"
                        previewStyle="height: 80px; width: 80px; object-fit: cover;"
                    />
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
                    <x-post.form-toggles 
                        :spoilerClass="$spoilerClass" 
                        :lockClass="$lockClass"
                        :spoilerId="$spoilerId"
                        :lockId="$lockId"
                        :spoilerChecked="$spoilerChecked"
                        :lockChecked="$lockChecked"
                    />
                    
                    <x-post.form-actions 
                        :clearClass="$clearClass"
                        :submitClass="$submitClass"
                        :spinnerClass="$spinnerClass"
                        :clearLabel="$clearLabel"
                        :submitLabel="$submitLabel"
                    />
                </div>
            </div>
        </div>
    @else
        @if($isComment)
            <div class="card shadow-sm mb-3 border-0 bg-light text-center p-3">
                <p class="text-muted mb-0 small">
                    {!! __('posts.login_to_comment', [
                        'login' => '<a href="' . route('login') . '" class="text-decoration-none fw-bold">' . __('common.login') . '</a>'
                    ]) !!}
                </p>
            </div>
        @else
            <div class="card shadow-sm mb-4 border-0 bg-light text-center p-4">
                <p class="text-muted mb-0">
                    {!! __('posts.login_or_register_to_post', [
                        'login' => '<a href="' . route('login') . '" class="text-decoration-none fw-bold">' . __('common.login') . '</a>',
                        'register' => '<a href="' . route('register') . '" class="text-decoration-none fw-bold">' . __('common.register') . '</a>'
                    ]) !!}
                </p>
            </div>
        @endif
    @endauth
@endif
