<x-layout headtitle="Edit Profile">
<style>
    /* Spoiler-style Hover Overlay Logic */
    .hover-overlay-container {
        position: relative;
    }
    .hover-overlay-container:hover .hover-overlay {
        opacity: 1 !important;
    }
    .hover-overlay {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
        background: rgba(0, 0, 0, 0.65);
    }
    
    /* Reveal animation for the save button */
    .reveal-btn {
        animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@php
    // Color generation logic
    $bannerHash = md5($user->username . '-banner');
    $bannerColor1 = '#' . substr($bannerHash, 0, 6);
    $bannerColor2 = '#' . substr($bannerHash, 6, 6);

    $avatarHash = md5($user->username . '-avatar');
    $avatarColor = '#' . substr($avatarHash, 0, 6);
@endphp

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-8">

                {{-- MEDIA UPLOAD FORM --}}
                <form method="POST" action="{{ route('profile.media.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden position-relative">
                        
                        <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                            <button type="submit" class="btn btn-primary fw-bold shadow-lg d-none reveal-btn" id="saveImagesBtn">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Images
                            </button>
                        </div>

                        <div class="hover-overlay-container position-relative w-100" 
                             style="cursor: pointer; height: 250px; background: linear-gradient(135deg, {{ $bannerColor1 }}, {{ $bannerColor2 }});" 
                             onclick="document.getElementById('bannerInput').click()">
                            
                            <img src="{{ $user->banner ? asset($user->banner) : '' }}" id="bannerPreview" class="w-100 h-100 object-fit-cover {{ $user->banner ? '' : 'd-none' }}" alt="Banner">
                            
                            <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                                <div class="text-center">
                                    <i class="bi bi-image fs-3 mb-1"></i>
                                    <div class="fw-semibold">Change Banner</div>
                                </div>
                            </div>
                            <input type="file" name="banner" id="bannerInput" class="d-none" accept="image/*" onchange="previewProfileImage(this, 'bannerPreview')">
                        </div>

                        <div class="card-body position-relative p-4">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-end gap-4">

                                <div class="hover-overlay-container position-relative rounded-circle shadow border border-4 border-white bg-white" 
                                     style="width: 160px; height: 160px; cursor: pointer; overflow: hidden; z-index: 2; margin-top: -90px;" 
                                     onclick="document.getElementById('avatarInput').click()">
                                    
                                    <img src="{{ $user->avatar ? asset($user->avatar) : '' }}" id="avatarPreview" class="w-100 h-100 object-fit-cover {{ $user->avatar ? '' : 'd-none' }}" alt="Avatar">
                                    
                                    @if(!$user->avatar)
                                        <div id="avatarFallback" class="w-100 h-100 text-white d-flex align-items-center justify-content-center" style="font-size: 4.5rem; background-color: {{ $avatarColor }};">
                                            {{ strtoupper(substr($user->username ?? '?', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                                        <div class="text-center">
                                            <i class="bi bi-camera-fill fs-4 mb-1"></i>
                                            <div style="font-size: 0.75rem;" class="fw-semibold">Change Avatar</div>
                                        </div>
                                    </div>
                                    <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewProfileImage(this, 'avatarPreview', 'avatarFallback')">
                                </div>

                                <div class="pb-lg-2 flex-grow-1">
                                    <h1 class="display-5 fw-bold mb-1">Edit Profile</h1>
                                    <p class="text-muted fs-5 mb-0">Customize your VGDB account and media</p>
                                    
                                    <div class="mt-2">
                                        @error('avatar') <div class="text-danger small fw-bold"><i class="bi bi-exclamation-circle"></i> Avatar: {{ $message }}</div> @enderror
                                        @error('banner') <div class="text-danger small fw-bold"><i class="bi bi-exclamation-circle"></i> Banner: {{ $message }}</div> @enderror
                                        @if(session('status') === 'profile-media-updated')
                                            <div class="text-success small fw-bold"><i class="bi bi-check-circle"></i> Media updated successfully.</div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1">Profile Information</h3>
                            <p class="text-muted mb-0">Update your account details and public profile</p>
                        </div>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="mb-4">
                            <h3 class="fw-bold mb-1">Privacy Settings</h3>
                            <p class="text-muted mb-0">Control who can view your content</p>
                        </div>
                        <form method="POST" action="/profile/privacy" class="row g-4">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Profile Visibility</label>
                                <select name="profile_visibility" class="form-select form-select-lg">
                                    <option value="public" {{ auth()->user()->settings->profile_visibility === 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="followers" {{ auth()->user()->settings->profile_visibility === 'followers' ? 'selected' : '' }}>Followers Only</option>
                                    <option value="mutuals" {{ auth()->user()->settings->profile_visibility === 'mutuals' ? 'selected' : '' }}>Mutuals Only</option>
                                    <option value="private" {{ auth()->user()->settings->profile_visibility === 'private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Playlist Visibility</label>
                                <select name="playlist_visibility" class="form-select form-select-lg">
                                    <option value="public" {{ auth()->user()->settings->playlist_visibility === 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="followers" {{ auth()->user()->settings->playlist_visibility === 'followers' ? 'selected' : '' }}>Followers Only</option>
                                    <option value="mutuals" {{ auth()->user()->settings->playlist_visibility === 'mutuals' ? 'selected' : '' }}>Mutuals Only</option>
                                    <option value="private" {{ auth()->user()->settings->playlist_visibility === 'private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-info btn-lg px-5 text-white">
                                    <i class="bi bi-shield-lock-fill me-2"></i> Save Privacy Settings
                                </button>
                                @if(session('status') === 'privacy-updated')
                                    <span class="text-success fw-semibold ms-3">Privacy settings updated successfully.</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4 p-lg-5">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="card shadow-sm border-0 border-danger">
                    <div class="card-body p-4 p-lg-5">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function previewProfileImage(input, previewId, fallbackId = null) {
            const preview = document.getElementById(previewId);
            const fallback = fallbackId ? document.getElementById(fallbackId) : null;
            const saveBtn = document.getElementById('saveImagesBtn');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    if (fallback) fallback.classList.add('d-none');
                    saveBtn.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-layout>