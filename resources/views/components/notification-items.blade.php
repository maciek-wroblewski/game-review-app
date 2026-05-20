@foreach($notifications as $notification)
    <li>
        <x-clickable-card href="{{ $notification->target_url }}" class="dropdown-item rounded-4 py-3 {{ !$notification->read ? 'bg-light' : '' }}">
            <div class="d-flex align-items-start gap-3">
                <x-user.static-avatar :user="$notification->fromUser" size="42px" />
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