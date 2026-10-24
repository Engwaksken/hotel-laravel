@php($unread = auth()->user()->unreadNotifications()->latest()->limit(8)->get())
<div class="notification-centre" data-notification-centre>
    <button type="button" class="notification-bell" data-notification-toggle aria-label="Notifications">
        <i class="fas fa-bell"></i>
        @if(auth()->user()->unreadNotifications()->count())<span class="notification-count">{{ min(auth()->user()->unreadNotifications()->count(),99) }}</span>@endif
    </button>
    <div class="notification-menu" data-notification-menu>
        <div class="notification-menu-head"><strong>Notifications</strong>@if($unread->isNotEmpty())<form method="POST" action="{{ route('staff-notifications.read-all') }}">@csrf<button class="link-btn" type="submit">Mark all read</button></form>@endif</div>
        <div class="notification-list">
        @forelse($unread as $notification)
            <form method="POST" action="{{ route('staff-notifications.read',$notification->id) }}" class="notification-item-form">@csrf<button type="submit" class="notification-item"><span class="notification-item-icon"><i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }}"></i></span><span><b>{{ $notification->data['title'] ?? 'Notification' }}</b><small>{{ $notification->data['message'] ?? '' }}</small>@if(!empty($notification->data['due_at']))<em>Due {{ $notification->data['due_at'] }}</em>@endif</span></button></form>
        @empty <div class="notification-empty"><i class="far fa-bell"></i><span>No new notifications.</span></div> @endforelse
        </div>
    </div>
</div>
