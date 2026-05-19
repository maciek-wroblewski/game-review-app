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

                <div class="card shadow-sm border-0 mb-4 overflow-hidden">

                    <div class="hover-overlay-container position-relative" 
                         style="cursor: pointer;" 
                         data-bs-toggle="modal" 
                         data-bs-target="#changeBannerModal">
                        <x-user.banner :user="$user" layout="full" />
                        <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                            <div class="text-center">
                                <i class="bi bi-camera-fill fs-3 mb-1"></i>
                                <div class="fw-semibold">Update Banner Image</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body position-relative p-4">

                        <div class="d-flex flex-column flex-lg-row align-items-lg-end gap-4">

                            <div class="hover-overlay-container position-relative rounded-circle shadow border border-4 border-white" 
                                 style="width: 160px; height: 160px; cursor: pointer; overflow: hidden; z-index: 2;" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#changeAvatarModal">
                                @if($user->avatar)
                                    <img src="{{ asset($user->avatar) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $user->username }}'s Avatar">
                                @else
                                    <div class="w-100 h-100 text-white d-flex align-items-center justify-content-center" style="font-size: 4.5rem; background-color: {{ $avatarColor }};">
                                        {{ strtoupper(substr($user->username ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                                    <div class="text-center">
                                        <i class="bi bi-camera-fill fs-4 mb-1"></i>
                                        <div style="font-size: 0.75rem;" class="fw-semibold">Update Avatar</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pb-lg-2">

                                <h1 class="display-5 fw-bold mb-1">
                                    Edit Profile
                                </h1>

                                <p class="text-muted fs-5 mb-0">
                                    Customize your VGDB account and privacy settings
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-body p-4 p-lg-5">

                        <div class="mb-4">

                            <h3 class="fw-bold mb-1">
                                Profile Information
                            </h3>

                            <p class="text-muted mb-0">
                                Update your account details and public profile
                            </p>

                        </div>

                        @include('profile.partials.update-profile-information-form')

                    </div>

                </div>

                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-body p-4 p-lg-5">

                        <div class="mb-4">

                            <h3 class="fw-bold mb-1">
                                Privacy Settings
                            </h3>

                            <p class="text-muted mb-0">
                                Control who can view your content
                            </p>

                        </div>

                        <form method="POST"
                              action="/profile/privacy"
                              class="row g-4">

                            @csrf
                            @method('PATCH')

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Profile Visibility
                                </label>

                                <select name="profile_visibility"
                                        class="form-select form-select-lg">

                                    <option value="public"
                                        {{ auth()->user()->settings->profile_visibility === 'public' ? 'selected' : '' }}>

                                        Public

                                    </option>

                                    <option value="followers"
                                        {{ auth()->user()->settings->profile_visibility === 'followers' ? 'selected' : '' }}>

                                        Followers Only

                                    </option>

                                    <option value="mutuals"
                                        {{ auth()->user()->settings->profile_visibility === 'mutuals' ? 'selected' : '' }}>

                                        Mutuals Only

                                    </option>

                                    <option value="private"
                                        {{ auth()->user()->settings->profile_visibility === 'private' ? 'selected' : '' }}>

                                        Private

                                    </option>

                                </select>

                            </div>

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Playlist Visibility
                                </label>

                                <select name="playlist_visibility"
                                        class="form-select form-select-lg">

                                    <option value="public"
                                        {{ auth()->user()->settings->playlist_visibility === 'public' ? 'selected' : '' }}>

                                        Public

                                    </option>

                                    <option value="followers"
                                        {{ auth()->user()->settings->playlist_visibility === 'followers' ? 'selected' : '' }}>

                                        Followers Only

                                    </option>

                                    <option value="mutuals"
                                        {{ auth()->user()->settings->playlist_visibility === 'mutuals' ? 'selected' : '' }}>

                                        Mutuals Only

                                    </option>

                                    <option value="private"
                                        {{ auth()->user()->settings->playlist_visibility === 'private' ? 'selected' : '' }}>

                                        Private

                                    </option>

                                </select>

                            </div>

                            <div class="col-12">

                                <button type="submit"
                                        class="btn btn-info btn-lg px-5 text-white">

                                    <i class="bi bi-shield-lock-fill me-2"></i>
                                    Save Privacy Settings

                                </button>

                                @if(session('status') === 'privacy-updated')

                                    <span class="text-success fw-semibold ms-3">

                                        Privacy settings updated successfully.

                                    </span>

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

    <div class="modal fade" id="changeAvatarModal" tabindex="-1" aria-labelledby="changeAvatarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="changeAvatarModalLabel">Change Avatar Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <form method="POST" action="{{ route('profile.media.update') }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-4 text-center">
                            <x-media-upload 
                                :multiple="false" 
                                inputName="avatar_media_id" 
                                accept="image/*"
                                previewStyle="height: 140px; width: 140px; object-fit: cover;"
                                previewClass="rounded-circle border bg-dark mx-auto" />
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Avatar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeBannerModal" tabindex="-1" aria-labelledby="changeBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="changeBannerModalLabel">Change Banner Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <form method="POST" action="{{ route('profile.media.update') }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-4">
                            <x-media-upload 
                                :multiple="false" 
                                inputName="banner_media_id" 
                                accept="image/*"
                                previewStyle="height: 180px; width: 100%; object-fit: cover;"
                                previewClass="rounded border bg-dark" />
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Banner</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout>