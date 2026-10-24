@php
    use App\Support\HotelFeatureSetting;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $commentsEnabled = HotelFeatureSetting::bool(
        'comments.enabled',
        true
    );

    $showHomepageComments = HotelFeatureSetting::bool(
        'comments.show_homepage',
        true
    );

    $approvedComments = collect();

    if (
        $commentsEnabled
        && $showHomepageComments
        && Schema::hasTable('guest_comments')
    ) {
        $approvedComments = DB::table('guest_comments')
            ->where('status', 'approved')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }
@endphp

@if($commentsEnabled)
<section class="guest-comments-section">
    <div class="guest-comments-heading">
        <span>Guest Feedback</span>
        <h2>What our guests say</h2>
        <p>
            Share your experience with us. New comments are reviewed before
            appearing publicly.
        </p>
    </div>

    @if($approvedComments->isNotEmpty())
        <div class="guest-comment-grid">
            @foreach($approvedComments as $comment)
                <article class="guest-comment-card">
                    <div class="guest-comment-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="{{ $i <= $comment->rating ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </div>

                    <p>
                        “{{ $comment->comment }}”
                    </p>

                    <strong>{{ $comment->guest_name }}</strong>
                </article>
            @endforeach
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('public.comments.store') }}"
        class="guest-comment-form"
    >
        @csrf

        <div class="guest-comment-fields">
            <div>
                <label>Your name</label>
                <input
                    type="text"
                    name="guest_name"
                    value="{{ old('guest_name') }}"
                    required
                >
            </div>

            <div>
                <label>Email <small>(optional)</small></label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                >
            </div>

            <div>
                <label>Rating</label>
                <select name="rating" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>
        </div>

        <div>
            <label>Your comment</label>
            <textarea
                name="comment"
                rows="4"
                minlength="10"
                maxlength="2000"
                required
            >{{ old('comment') }}</textarea>
        </div>

        <button type="submit">
            <i class="fas fa-paper-plane"></i>
            Submit Comment
        </button>
    </form>
</section>
@endif
