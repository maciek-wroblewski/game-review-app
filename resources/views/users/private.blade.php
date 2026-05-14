<x-layout headtitle="Private Profile">

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-6">

                <div class="card shadow-sm border-0 text-center">

                    <div class="card-body p-5">

                        <div class="mb-4">

                            <div class="rounded-circle bg-dark text-white
                                        d-inline-flex align-items-center justify-content-center shadow"
                                 style="
                                    width: 120px;
                                    height: 120px;
                                    font-size: 3rem;
                                 ">

                                {{ strtoupper(substr($user->username, 0, 1)) }}

                            </div>

                        </div>

                        <h1 class="fw-bold mb-2">
                            {{ $user->username }}
                        </h1>

                        <p class="text-muted fs-5 mb-4">
                            This profile is private.
                        </p>

                        <div class="alert alert-secondary border-0">

                            <i class="bi bi-lock-fill me-2"></i>

                            You do not have permission to view this profile.

                        </div>

                        @auth

                            @if(auth()->id() !== $user->id)

                                <form method="POST"
                                      action="/users/{{ $user->id }}/follow">

                                    @csrf

                                    <button class="btn btn-primary btn-lg px-5">

                                        <i class="bi bi-person-plus-fill me-2"></i>
                                        Follow User

                                    </button>

                                </form>

                            @endif

                        @endauth

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>