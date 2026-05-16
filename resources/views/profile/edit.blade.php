<x-layout headtitle="Edit Profile">
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

                <!-- Header -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden">

                    @if($user->banner)
                        <img src="{{ asset($user->banner) }}" class="w-100" style="height: 220px; object-fit: cover;" alt="{{ $user->username }}'s Banner">
                    @else
                        <div style="height: 220px; background: linear-gradient(135deg, {{ $bannerColor1 }} 0%, {{ $bannerColor2 }} 100%);"></div>
                    @endif

                    <div class="card-body position-relative p-4">

                        <div class="d-flex flex-column flex-lg-row align-items-lg-end gap-4">

                            <!-- Avatar -->
                            @if($user->avatar)
                                <img src="{{ asset($user->avatar) }}" 
                                    class="rounded-circle shadow border border-4 border-white"
                                    style="width: 160px; height: 160px; margin-top: -110px; flex-shrink: 0; object-fit: cover; position: relative; z-index: 2;"
                                    alt="{{ $user->username }}'s Avatar">
                            @else
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow border border-4 border-white 'mx-auto'"
                                    style="width: -110px; height: -110px; font-size: 4.5rem; margin-top: -110px; flex-shrink: 0; background-color: {{ $avatarColor }}; position: relative; z-index: 2;">
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                </div>
                            @endif

                            <!-- Header Text -->
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

                <!-- Profile Information -->
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

                <!-- Privacy Settings -->
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

                            <!-- Profile Visibility -->
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

                            <!-- Playlist Visibility -->
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

                            <!-- Save -->
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

                <!-- Password -->
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-body p-4 p-lg-5">

                        @include('profile.partials.update-password-form')

                    </div>

                </div>

                <!-- Delete Account -->
                <div class="card shadow-sm border-0 border-danger">

                    <div class="card-body p-4 p-lg-5">

                        @include('profile.partials.delete-user-form')

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>