<section>

    <form method="post"
          action="{{ route('profile.update') }}"
          class="row g-4">

        @csrf
        @method('patch')

        <!-- Username -->
        <div class="col-12">

            <label for="username" class="form-label fw-semibold">
                Username
            </label>

            <input id="username"
                   name="username"
                   type="text"
                   class="form-control form-control-lg"
                   value="{{ old('username', $user->username) }}"
                   required>

            @error('username')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>


        <!-- Email -->
        <div class="col-12">

            <label for="email" class="form-label fw-semibold">
                Email Address
            </label>

            <input id="email"
                   name="email"
                   type="email"
                   class="form-control form-control-lg"
                   value="{{ old('email', $user->email) }}"
                   required>

            @error('email')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <!-- Bio -->
        <div class="col-12">

            <label for="bio" class="form-label fw-semibold">
                Bio
            </label>

            <textarea id="bio"
                      name="bio"
                      rows="5"
                      class="form-control">{{ old('bio', $user->bio) }}</textarea>

            @error('bio')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <!-- Save Button -->
        <div class="col-12">

            <button type="submit"
                    class="btn btn-primary btn-lg px-5">

                Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success ms-3 fw-semibold">
                    Profile updated successfully.
                </span>
            @endif

        </div>

    </form>

</section>
