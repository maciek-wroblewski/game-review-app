<x-layout headtitle="{{ __('auth.login') }}">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-dark text-white p-5 text-center">
                        <i class="bi bi-controller display-3 text-primary"></i>
                        <h1 class="fw-bold mt-3 mb-2">
                            {{ __('auth.welcome_back') }}
                        </h1>
                        <p class="text-secondary mb-0">
                            {{ __('auth.log_into_account') }}
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
                                    {{ __('auth.email_address') }}
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
                            <!-- Remember -->
                            <div class="form-check mb-4">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="remember"
                                       id="remember_me">
                                <label class="form-check-label"
                                       for="remember_me">
                                    {{ __('auth.remember_me') }}
                                </label>
                            </div>
                            <!-- Bottom -->
                            <div class="d-flex justify-content-between align-items-center mt-5">
                                @if (Route::has('password.request'))
                                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                                        {{ __('auth.forgot_password') }}
                                    </a>
                                @endif
                                <button type="submit"
                                        class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    {{ __('auth.log_in') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>