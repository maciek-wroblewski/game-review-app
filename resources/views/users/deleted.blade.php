<x-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4 p-5 text-center bg-white">
                    <div class="card-body">
                        <div class="text-danger mb-3">
                            <i class="bi bi-trash-fill" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="fw-bold mb-2 text-dark">{{ __('users.deleted_user') }}</h3>
                        <p class="text-muted mb-4">{{ __('common.this_user_has_been_deleted') }}</p>
                        <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4">
                            {{ __('common.home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
