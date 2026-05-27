@foreach($notifications as $notification)
    <li>
        <x-clickable-card href="{{ $notification->target_url }}" class="dropdown-item rounded-4 py-3 {{ !$notification->read ? 'bg-light' : '' }}">
            <div class="d-flex align-items-start gap-3">
                <a href="/users/{{ optional($notification->fromUser)->username }}" class="text-decoration-none">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center overflow-hidden" style="width: 42px; height: 42px; flex-shrink: 0;">
                        @php
                            $avatar = optional($notification->fromUser)->avatar;
                            $avatarPath = is_string($avatar) ? $avatar : optional($avatar)->file_path;
                        @endphp

                        @if($avatarPath)
                            <img src="{{ $avatarPath }}" class="w-100 h-100 object-fit-cover">
                        @else
                            {{ strtoupper(substr(optional($notification->fromUser)->username ?? 'S', 0, 1)) }}
                        @endif
                    </div>
                </a>
                <div class="flex-grow-1 text-wrap">
                    <div class="small mb-1 text-dark">
                        <a href="/users/{{ optional($notification->fromUser)->username }}" class="fw-semibold text-decoration-none text-dark">
                            {{ optional($notification->fromUser)->username }}
                        </a>
                        {{ str_replace(optional($notification->fromUser)->username, '', $notification->message) }}
                    </div>
                    <small class="text-muted">
                        {{ $notification->created_at->diffForHumans() }}
                    </small>
                </div>
            </div>
        </x-clickable-card>
    </li>
@endforeach