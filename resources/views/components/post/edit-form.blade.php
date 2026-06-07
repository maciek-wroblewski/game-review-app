@props(['post'])



{{-- Add data-open="false" here to track the state --}}
<div class="js-edit-container" data-open="false">
    <div class="js-edit-mode border-top border-light p-3 bg-light">
        <div class="flex-grow-1">
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">{{ __('posts.post_edit') }}</label>
                <textarea class="js-edit-textarea form-control" rows="4">{{ $post->body }}</textarea>
            </div>

            <div class="mb-3 border-bottom pb-3">
                <label class="form-label fw-bold text-muted small">{{ __('posts.media') }}</label>
                <x-media-upload 
                    multiple="true" 
                    inputName="media_ids[]" 
                    accept="image/*,video/mp4" 
                    :existingMedia="$post->media"
                    previewClass="rounded border bg-dark"
                    previewStyle="height: 80px; width: 80px; object-fit: cover;"
                />
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
                <x-post.form-toggles 
                    :spoilerClass="'js-edit-spoiler'" 
                    :lockClass="'js-edit-locked'"
                    :spoilerId="'isSpoiler-' . $post->id"
                    :lockId="'isLocked-' . $post->id"
                    :spoilerChecked="$post->is_spoiler"
                    :lockChecked="$post->is_locked"
                />
                
                <x-post.form-actions 
                    :clearClass="'js-btn-cancel'"
                    :submitClass="'js-btn-save'"
                    :spinnerClass="'js-save-spinner'"
                    :clearLabel="__('common.cancel')"
                    :submitLabel="__('common.save_changes')"
                />
            </div>
        </div>
    </div>
</div>
