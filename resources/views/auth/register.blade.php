<x-layout headtitle="{{ __('auth.register') }}">

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-md-7 col-lg-6">

                <div class="card border-0 shadow-sm overflow-hidden">

                    <!-- Header -->
                    <div class="bg-dark text-white p-5 text-center">

                        <i class="bi bi-controller display-3 text-primary"></i>

                        <h1 class="fw-bold mt-3 mb-2">
                            {{ __('auth.create_account') }}
                        </h1>

                        <p class="text-secondary mb-0">
                            {{ __('auth.join_community') }}
                        </p>

                    </div>

                    <!-- Form -->
                    <div class="card-body p-5">

                        <form method="POST"
                              action="{{ route('register') }}">

                            @csrf

                            <!-- Username -->
                            <div class="mb-4">

                                <label for="username"
                                       class="form-label fw-semibold">

                                    {{ __('auth.username') }}

                                </label>

                                <input id="username"
                                       type="text"
                                       name="username"
                                       value="{{ old('username') }}"
                                       required
                                       autofocus
                                       class="form-control form-control-lg">

                                @error('username')

                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            <!-- Email -->
                            <div class="mb-4">

                                <label for="email"
                                       class="form-label fw-semibold">

                                    {{ __('auth.email_address') }}

                                </label>

                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       class="form-control form-control-lg">

                                @error('email')

                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            <!-- Password -->
                            <div class="mb-4">

                                <label for="password"
                                       class="form-label fw-semibold">

                                    {{ __('auth.password') }}

                                </label>

                                <input id="password"
                                       type="password"
                                       name="password"
                                       required
                                       class="form-control form-control-lg">

                                @error('password')

                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">

                                <label for="password_confirmation"
                                       class="form-label fw-semibold">

                                    {{ __('auth.confirm_password') }}

                                </label>

                                <input id="password_confirmation"
                                       type="password"
                                       name="password_confirmation"
                                       required
                                       class="form-control form-control-lg">

                            </div>

                            <!-- Bottom -->
                            <div class="d-flex justify-content-between align-items-center mt-5">

                                <a href="{{ route('login') }}"
                                   class="text-decoration-none">

                                    {{ __('auth.already_registered') }}

                                </a>

                                <button type="submit"
                                        class="btn btn-primary btn-lg px-4">

                                    <i class="bi bi-person-plus-fill me-2"></i>
                                    {{ __('auth.create_account') }}

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>