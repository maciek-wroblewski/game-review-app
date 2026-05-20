<x-layout headtitle="{{ __('auth.forgot_password_title') }}">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-dark text-white p-5 text-center">
                        <i class="bi bi-key display-3 text-primary"></i>
                        <h1 class="fw-bold mt-3 mb-2">
                            {{ __('auth.forgot_password_title') }}
                        </h1>
                        <p class="text-secondary mb-0">
                            {{ __('auth.no_problem_reset') }}
                        </p>
                    </div>
                    <!-- Form -->
                    <div class="card-body p-5 py-6">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
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

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-envelope me-2"></i>
                                    {{ __('auth.send_reset_link') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>