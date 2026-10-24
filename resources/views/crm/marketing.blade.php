@extends('layouts.app')
@section('title', 'Marketing')
@section('page_title', 'Marketing Communications')
@section('content')
    <div class="page-head"><div><h1>Marketing communications</h1><p>Campaign audiences honor recorded consent for each communication channel.</p></div>
        <button class="btn primary" type="button" data-modal="newCampaign">Create campaign</button>
    </div>
    <div class="card table-wrap"><table><thead><tr><th>Campaign</th><th>Channel</th><th>Status</th><th>Recipients</th><th>Schedule</th><th>Action</th></tr></thead><tbody>
        @forelse($campaigns as $campaign)<tr><td>{{ $campaign->name }}</td><td>{{ strtoupper($campaign->channel) }}</td><td>{{ ucfirst($campaign->status) }}</td><td>{{ $campaign->recipients_count }}</td><td>{{ $campaign->scheduled_at?->format('d M Y H:i') }}</td><td><form method="POST" action="{{ route('crm.marketing.prepare', $campaign) }}">@csrf<button class="btn small">Prepare queue</button></form></td></tr>
        @empty<tr><td colspan="6" class="empty">No campaigns found.</td></tr>@endforelse
    </tbody></table>{{ $campaigns->links() }}</div>
    <div class="modal" id="newCampaign"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Create campaign</h2>
        <form method="POST" action="{{ route('crm.marketing.store') }}" class="form-grid">@csrf
            <label>Campaign name<input name="name" required></label>
            <label>Channel<select name="channel"><option value="email">Email</option><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option><option value="push">Push</option></select></label>
            <label class="span-2">Subject (email)<input name="subject"></label>
            <label class="span-2">Message<textarea name="message" required></textarea></label>
            <label>Schedule (optional)<input type="datetime-local" name="scheduled_at"></label>
            <div class="actions"><button class="btn primary">Create campaign</button></div>
        </form>
    </div></div>
@endsection
