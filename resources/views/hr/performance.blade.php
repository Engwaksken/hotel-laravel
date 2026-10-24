@extends('layouts.app')
@section('title', 'Performance')
@section('page_title', 'Performance Reviews')
@section('content')
    <div class="page-head"><div><h1>Performance reviews</h1><p>Record and review employee feedback, scores and goals.</p></div><button class="btn primary" type="button" data-modal="newPerformanceReview">Record review</button></div>
    <div class="card table-wrap"><table><thead><tr><th>Date</th><th>Employee</th><th>Score</th><th>Strengths</th><th>Goals</th></tr></thead><tbody>
        @forelse($reviews as $review)<tr><td>{{ $review->review_date->format('d M Y') }}</td><td>{{ $review->employee?->full_name }}</td><td>{{ $review->overall_score }}%</td><td>{{ Str::limit($review->strengths, 70) }}</td><td>{{ Str::limit($review->goals, 70) }}</td></tr>
        @empty<tr><td colspan="5" class="empty">No performance reviews recorded.</td></tr>@endforelse
    </tbody></table>{{ $reviews->links() }}</div>
    <div class="modal" id="newPerformanceReview"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Record performance review</h2><form method="POST" action="{{ route('hr.performance.store') }}" class="form-grid">@csrf
        <label>Employee<select name="employee_id" required>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->full_name }}</option>@endforeach</select></label><label>Review date<input type="date" name="review_date" value="{{ today()->toDateString() }}" required></label>
        <label>Score / 100<input type="number" step="0.1" min="0" max="100" name="overall_score"></label><label>Strengths<textarea name="strengths"></textarea></label><label>Improvement areas<textarea name="improvement_areas"></textarea></label><label>Goals<textarea name="goals"></textarea></label><div class="actions"><button class="btn primary">Save review</button></div>
    </form></div></div>
@endsection
