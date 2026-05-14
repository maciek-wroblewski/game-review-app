<section>

    <form method="post"
          action="{{ route('password.update') }}"
          class="row g-4">

        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="col-12">

            <label for="update_password_current_password"
                   class="form-label fw-semibold">

                Current Password

            </label>

            <input id="update_password_current_password"
                   name="current_password"
                   type="password"
                   class="form-control form-control-lg"
                   autocomplete="current-password">

            @error('current_password', 'updatePassword')

                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>

            @enderror

        </div>

        <!-- New Password -->
        <div class="col-12">

            <label for="update_password_password"
                   class="form-label fw-semibold">

                New Password

            </label>

            <input id="update_password_password"
                   name="password"
                   type="password"
                   class="form-control form-control-lg"
                   autocomplete="new-password">

            @error('password', 'updatePassword')

                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>

            @enderror

        </div>

        <!-- Confirm Password -->
        <div class="col-12">

            <label for="update_password_password_confirmation"
                   class="form-label fw-semibold">

                Confirm New Password

            </label>

            <input id="update_password_password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="form-control form-control-lg"
                   autocomplete="new-password">

            @error('password_confirmation', 'updatePassword')

                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>

            @enderror

        </div>

        <!-- Save -->
        <div class="col-12 d-flex align-items-center gap-3 pt-2">

            <button type="submit"
                    class="btn btn-warning btn-lg px-5 shadow-sm fw-semibold">

                <i class="bi bi-shield-lock-fill me-2"></i>
                Update Password

            </button>

            @if (session('status') === 'password-updated')

                <span class="text-success fw-semibold">

                    <i class="bi bi-check-circle-fill me-1"></i>
                    Password updated successfully

                </span>

            @endif

        </div>

    </form>

</section>