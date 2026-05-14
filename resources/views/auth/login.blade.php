<x-layout headtitle="Login">

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-md-7 col-lg-6">

                <div class="card border-0 shadow-sm overflow-hidden">

                    <!-- Header -->
                    <div class="bg-dark text-white p-5 text-center">

                        <i class="bi bi-controller display-3 text-primary"></i>

                        <h1 class="fw-bold mt-3 mb-2">
                            Welcome Back
                        </h1>

                        <p class="text-secondary mb-0">
                            Log into your VGDB account
                        </p>

                    </div>

                    <!-- Form -->
                    <div class="card-body p-5 py-6">

                        @if (session('status'))

                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>

                        @endif

                        <form method="POST"
                              action="{{ route('login') }}">

                            @csrf

                            <!-- Email -->
                            <div class="mb-4">

                                <label for="email"
                                       class="form-label fw-semibold">

                                    Email Address

                                </label>

                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus
                                       class="form-control form-control-lg">

                                @error('email')

                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                            <!-- Password -->
                            <div class="mb-5">

                                <label for="password"
                                       class="form-label fw-semibold">

                                    Password

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

                            <!-- Remember -->
                            <div class="form-check mb-4">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="remember"
                                       id="remember_me">

                                <label class="form-check-label"
                                       for="remember_me">

                                    Remember me

                                </label>

                            </div>

                            <!-- Bottom -->
                            <div class="d-flex justify-content-between align-items-center mt-5">

                                @if (Route::has('password.request'))

                                    <a class="text-decoration-none"
                                       href="{{ route('password.request') }}">

                                        Forgot password?

                                    </a>

                                @endif

                                <button type="submit"
                                        class="btn btn-primary btn-lg px-4">

                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Log In

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>