<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Book · {{ $web->property->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/public-hotel.css') }}">
</head>
<body>
<header class="site-nav">
    <a href="{{ route('public.hotel.property', $web->slug) }}">
        <strong>{{ $web->property->name }}</strong>
    </a>
</header>

<main class="booking">
    <h1>Book your stay</h1>
    <p>Choose your dates and room type to check availability.</p>

    @if ($errors->any())
        <div class="error" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($types->isEmpty())
        <div class="error" role="status">
            Online booking is currently unavailable. Please contact the hotel.
        </div>
    @else
        <form
            id="online-booking-form"
            method="post"
            action="{{ route('public.booking.store', $web->slug) }}"
            data-availability-url="{{ route('public.booking.availability', $web->slug) }}"
        >
            @csrf

            <div class="form-grid">
                <label for="check-in">
                    Check in
                    <input
                        id="check-in"
                        type="date"
                        name="check_in"
                        min="{{ now()->toDateString() }}"
                        value="{{ old('check_in') }}"
                        required
                    >
                </label>

                <label for="check-out">
                    Check out
                    <input
                        id="check-out"
                        type="date"
                        name="check_out"
                        min="{{ now()->addDay()->toDateString() }}"
                        value="{{ old('check_out') }}"
                        required
                    >
                </label>

                <label for="room-type">
                    Room type
                    <select id="room-type" name="room_type_id" required>
                        <option value="">Select a room type</option>
                        @foreach ($types as $type)
                            <option
                                value="{{ $type->id }}"
                                @selected((string) old('room_type_id', request('room_type_id')) === (string) $type->id)
                            >
                                {{ $type->name }} — UGX {{ number_format((float) $type->base_rate) }}/night
                            </option>
                        @endforeach
                    </select>
                </label>

                <label for="adults">
                    Adults
                    <input id="adults" type="number" name="adults" min="1" max="20" value="{{ old('adults', 1) }}" required>
                </label>

                <label for="children">
                    Children
                    <input id="children" type="number" name="children" min="0" max="20" value="{{ old('children', 0) }}">
                </label>

                <label for="first-name">
                    First name
                    <input id="first-name" name="first_name" autocomplete="given-name" maxlength="120" value="{{ old('first_name') }}" required>
                </label>

                <label for="last-name">
                    Last name
                    <input id="last-name" name="last_name" autocomplete="family-name" maxlength="120" value="{{ old('last_name') }}">
                </label>

                <label for="email">
                    Email
                    <input id="email" type="email" name="email" autocomplete="email" maxlength="190" value="{{ old('email') }}" required>
                </label>

                <label for="phone">
                    Phone
                    <input id="phone" type="tel" name="phone" autocomplete="tel" maxlength="50" value="{{ old('phone') }}" required>
                </label>

                <label for="nationality">
                    Nationality
                    <input id="nationality" name="nationality" autocomplete="country-name" maxlength="100" value="{{ old('nationality', 'Ugandan') }}">
                </label>
            </div>

            <p id="availability-feedback" class="availability-status" role="status" aria-live="polite">
                Choose your dates and room type to check availability.
            </p>

            @if ($addons->isNotEmpty())
                <h2>Add-ons</h2>
                <div class="grid">
                    @foreach ($addons as $addon)
                        <label class="card addon-card" for="addon-{{ $addon->id }}">
                            <span>{{ $addon->name }}</span>
                            @if ($addon->description)
                                <small>{{ $addon->description }}</small>
                            @endif
                            <b>UGX {{ number_format((float) $addon->price) }}</b>
                            <input
                                id="addon-{{ $addon->id }}"
                                type="number"
                                min="0"
                                max="20"
                                name="addons[{{ $addon->id }}]"
                                value="{{ old('addons.'.$addon->id, 0) }}"
                            >
                        </label>
                    @endforeach
                </div>
            @endif

            <label for="special-requests">
                Special requests
                <textarea id="special-requests" name="special_requests" maxlength="2000">{{ old('special_requests') }}</textarea>
            </label>

            <p>Required deposit: <b>{{ $web->default_deposit_percent }}%</b> of booking value.</p>
            <button id="confirm-booking" class="btn" type="submit">Confirm booking</button>
        </form>
    @endif
</main>

<script>
(() => {
    const form = document.getElementById('online-booking-form');
    if (!form) return;

    const checkIn = form.querySelector('[name="check_in"]');
    const checkOut = form.querySelector('[name="check_out"]');
    const roomType = form.querySelector('[name="room_type_id"]');
    const feedback = document.getElementById('availability-feedback');
    const submit = document.getElementById('confirm-booking');
    let timer;
    let request;

    function nextDate(value) {
        const date = new Date(`${value}T00:00:00`);
        date.setDate(date.getDate() + 1);
        return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
    }

    function scheduleAvailabilityCheck() {
        window.clearTimeout(timer);
        timer = window.setTimeout(checkAvailability, 250);
    }

    async function checkAvailability() {
        if (checkIn.value) {
            checkOut.min = nextDate(checkIn.value);
            if (checkOut.value && checkOut.value <= checkIn.value) checkOut.value = '';
        }

        if (!checkIn.value || !checkOut.value || !roomType.value) {
            feedback.textContent = 'Choose your dates and room type to check availability.';
            feedback.classList.remove('is-available', 'is-unavailable');
            submit.disabled = false;
            return;
        }

        if (checkOut.value <= checkIn.value) {
            feedback.textContent = 'Check-out must be at least one day after check-in.';
            feedback.classList.remove('is-available');
            feedback.classList.add('is-unavailable');
            submit.disabled = true;
            return;
        }

        request?.abort();
        request = new AbortController();
        submit.disabled = true;
        feedback.textContent = 'Checking room availability…';
        feedback.classList.remove('is-available', 'is-unavailable');

        const query = new URLSearchParams({
            room_type_id: roomType.value,
            check_in: checkIn.value,
            check_out: checkOut.value,
        });

        try {
            const response = await fetch(`${form.dataset.availabilityUrl}?${query}`, {
                headers: { Accept: 'application/json' },
                signal: request.signal,
            });

            if (!response.ok) throw new Error('Availability check failed');

            const result = await response.json();
            if (result.available) {
                const count = Number(result.available_rooms);
                feedback.textContent = `${count} room${count === 1 ? '' : 's'} available. Availability will be checked again when you confirm.`;
                feedback.classList.add('is-available');
                submit.disabled = false;
            } else {
                feedback.textContent = 'No rooms of this type are available for those dates. Try different dates or another room type.';
                feedback.classList.add('is-unavailable');
                submit.disabled = true;
            }
        } catch (error) {
            if (error.name === 'AbortError') return;
            feedback.textContent = 'Availability could not be checked right now. You can still submit; the hotel will verify it before confirming.';
            submit.disabled = false;
        }
    }

    [checkIn, checkOut, roomType].forEach((field) => {
        field.addEventListener('change', scheduleAvailabilityCheck);
    });

    form.addEventListener('submit', (event) => {
        if (checkOut.value && checkIn.value && checkOut.value <= checkIn.value) {
            event.preventDefault();
            checkOut.focus();
            scheduleAvailabilityCheck();
        }
    });

    if (checkIn.value && checkOut.value && roomType.value) scheduleAvailabilityCheck();
})();
</script>
</body>
</html>
