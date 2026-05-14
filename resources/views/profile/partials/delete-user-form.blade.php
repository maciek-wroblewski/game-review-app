<section>

    <div class="alert alert-danger border-0 shadow-sm mb-4">

        <h4 class="fw-bold mb-2">
            Delete Account
        </h4>

        <p class="mb-0">
            Once your account is deleted, all of your data, reviews,
            playlists and activity will be permanently removed.
            This action cannot be undone.
        </p>

    </div>

    <form method="post"
          action="{{ route('profile.destroy') }}"
          class="row g-4">

        @csrf
        @method('delete')

        <div class="col-12">

            <label for="password"
                   class="form-label fw-semibold">

                Confirm Your Password
            </label>

            <input id="password"
                   name="password"
                   type="password"
                   class="form-control form-control-lg"
                   placeholder="Enter your password to confirm">

            @error('password', 'userDeletion')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="col-12">

            <button type="submit"
                    class="btn btn-danger btn-lg px-5 fw-semibold">

                Delete Account
            </button>

        </div>

    </form>

</section>