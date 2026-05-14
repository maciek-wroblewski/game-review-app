<x-layout headtitle="Edit Profile">

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-xl-8">

                <!-- Header -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden">

                    <div class="bg-dark"
                         style="
                            height: 180px;
                            background: linear-gradient(135deg, #0d6efd 0%, #111827 100%);
                         ">
                    </div>

                    <div class="card-body position-relative p-4">

                        <div class="d-flex flex-column flex-lg-row align-items-lg-end gap-4">

                            <!-- Avatar -->
                            <div class="rounded-circle bg-primary text-white
                                        d-flex align-items-center justify-content-center
                                        shadow border border-4 border-white"
                                 style="
                                    width: 140px;
                                    height: 140px;
                                    font-size: 3.8rem;
                                    margin-top: -90px;
                                    flex-shrink: 0;
                                 ">

                                {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}

                            </div>

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