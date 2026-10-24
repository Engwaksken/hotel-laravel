@extends('layouts.app')
@section('title', 'Feedback')
@section('page_title', 'Guest Feedback')
@section('content')
    <div class="page-head"><div><h1>Guest feedback</h1><p>Capture guest ratings and comments after their stay.</p></div><button class="btn primary" type="button" data-modal="recordFeedback">Record feedback</button></div>
    <div class="card table-wrap"><table><thead><tr><th>Date</th><th>Guest</th><th>Overall</th><th>Room</th><th>Service</th><th>Food</th><th>Comments</th></tr></thead><tbody>
        @forelse($feedback as $entry)<tr><td>{{ $entry->submitted_at?->format('d M Y') }}</td><td>{{ $entry->guest?->full_name ?? '—' }}</td><td>{{ $entry->rating }}/5</td><td>{{ $entry->room_rating ?? '—' }}</td><td>{{ $entry->service_rating ?? '—' }}</td><td>{{ $entry->food_rating ?? '—' }}</td><td>{{ $entry->comments ?: '—' }}</td></tr>
        @empty<tr><td colspan="7" class="empty">No guest feedback recorded.</td></tr>@endforelse
    </tbody></table>{{ $feedback->links() }}</div>
    <div class="modal {{ $errors->any() ? 'open' : '' }}" id="recordFeedback"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Record guest feedback</h2>
        <form method="POST" action="{{ route('crm.feedback.store') }}" class="form-grid">@csrf
            <label class="span-2">Guest (optional)<select name="guest_id"><option value="">Select guest</option>@foreach($guests as $guest)<option value="{{ $guest->id }}">{{ $guest->full_name }}</option>@endforeach</select></label>
            <label>Overall rating (1–5)<input type="number" min="1" max="5" name="rating" required></label><label>Room rating (optional)<input type="number" min="1" max="5" name="room_rating"></label>
            <label>Service rating (optional)<input type="number" min="1" max="5" name="service_rating"></label><label>Food rating (optional)<input type="number" min="1" max="5" name="food_rating"></label>
            <label class="span-2">Comments<textarea name="comments"></textarea></label><label class="check span-2"><input type="checkbox" name="permission_to_publish" value="1"> Guest permits testimonial publication</label>
            <div class="actions"><button class="btn primary">Save feedback</button></div>
        </form>
    </div></div>
@endsection
