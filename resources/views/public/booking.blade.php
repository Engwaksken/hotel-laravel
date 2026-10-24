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
            data-initial-step="{{ $errors->hasAny(['check_in', 'check_out', 'room_type_id', 'adults', 'children']) ? 'stay' : ($errors->hasAny(['first_name', 'last_name', 'email', 'phone', 'nationality']) ? 'guest' : ($errors->any() ? 'extras' : 'stay')) }}"
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

    const originalGrid = form.querySelector('.form-grid');
    const guestFields = Array.from(originalGrid.querySelectorAll(':scope > label')).slice(5);
    const tabDefinitions = [
        ['stay', '1. Stay'],
        ['guest', '2. Guest details'],
        ['extras', '3. Extras & review'],
    ];
    const tabs = document.createElement('div');
    tabs.className = 'booking-tabs';
    tabs.setAttribute('role', 'tablist');
    tabs.setAttribute('aria-label', 'Booking steps');

    const panels = {};
    const tabButtons = {};
    for (const [key, title] of tabDefinitions) {
        const tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'booking-tab';
        tab.id = `booking-tab-${key}`;
        tab.textContent = title;
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', `booking-step-${key}`);
        tab.setAttribute('aria-selected', 'false');
        tabs.appendChild(tab);
        tabButtons[key] = tab;

        const panel = document.createElement('section');
        panel.className = 'booking-step';
        panel.id = `booking-step-${key}`;
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', tab.id);
        panel.hidden = true;
        panels[key] = panel;
    }

    const stayTitle = document.createElement('h2');
    stayTitle.textContent = 'Choose your stay';
    panels.stay.append(stayTitle, originalGrid);
    const guestGrid = document.createElement('div');
    guestGrid.className = 'form-grid';
    guestFields.forEach((field) => guestGrid.appendChild(field));
    const guestTitle = document.createElement('h2');
    guestTitle.textContent = 'Guest details';
    panels.guest.append(guestTitle, guestGrid);

    const availability = document.getElementById('availability-feedback');
    if (availability) panels.stay.appendChild(availability);

    const extrasTitle = form.querySelector('h2');
    if (extrasTitle) {
        extrasTitle.textContent = 'Optional extras';
        panels.extras.appendChild(extrasTitle);
    }
    const addonsGrid = form.querySelector('.grid');
    if (addonsGrid) panels.extras.appendChild(addonsGrid);
    const specialRequests = form.querySelector('[for="special-requests"]');
    if (specialRequests) panels.extras.appendChild(specialRequests);
    const depositNote = Array.from(form.children).find((element) =>
        element.matches('p') && element.textContent.includes('Required deposit')
    );
    if (depositNote) panels.extras.appendChild(depositNote);
    const submit = document.getElementById('confirm-booking');
    if (submit) panels.extras.appendChild(submit);

    const stayActions = document.createElement('div');
    stayActions.className = 'booking-step-actions booking-step-actions-end';
    stayActions.innerHTML = '<button type="button" class="btn" data-next="guest">Continue to guest details</button>';
    panels.stay.appendChild(stayActions);

    const guestActions = document.createElement('div');
    guestActions.className = 'booking-step-actions';
    guestActions.innerHTML = '<button type="button" class="btn btn-secondary" data-previous="stay">Back</button><button type="button" class="btn" data-next="extras">Continue to extras</button>';
    panels.guest.appendChild(guestActions);

    const extrasActions = document.createElement('div');
    extrasActions.className = 'booking-step-actions';
    extrasActions.innerHTML = '<button type="button" class="btn btn-secondary" data-previous="guest">Back</button>';
    panels.extras.insertBefore(extrasActions, submit);

    form.append(tabs, panels.stay, panels.guest, panels.extras);

    function activateStep(key, focusTab = false) {
        if (!panels[key]) return;
        for (const [step] of tabDefinitions) {
            const active = step === key;
            panels[step].hidden = !active;
            tabButtons[step].setAttribute('aria-selected', String(active));
            tabButtons[step].tabIndex = active ? 0 : -1;
        }
        if (focusTab) tabButtons[key].focus();
    }

    function validatePanel(panel) {
        const invalid = Array.from(panel.querySelectorAll(':invalid'))[0];
        if (!invalid) return true;
        invalid.reportValidity();
        return false;
    }

    tabs.addEventListener('click', (event) => {
        const button = event.target.closest('[role="tab"]');
        if (button) activateStep(button.id.replace('booking-tab-', ''));
    });

    form.querySelectorAll('[data-next]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = button.dataset.next;
            const current = button.closest('.booking-step').id.replace('booking-step-', '');
            if (validatePanel(panels[current])) {
                activateStep(next);
                tabButtons[next].focus();
            }
        });
    });
    form.querySelectorAll('[data-previous]').forEach((button) => {
        button.addEventListener('click', () => {
            activateStep(button.dataset.previous);
            tabButtons[button.dataset.previous].focus();
        });
    });

    form.noValidate = true;
    activateStep(form.dataset.initialStep || 'stay');

    const checkIn = form.querySelector('[name="check_in"]');
    const checkOut = form.querySelector('[name="check_out"]');
    const roomType = form.querySelector('[name="room_type_id"]');
    const feedback = document.getElementById('availability-feedback');
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
        const invalid = Array.from(form.querySelectorAll(':invalid'))[0];
        if (invalid) {
            event.preventDefault();
            const panel = invalid.closest('.booking-step');
            if (panel) activateStep(panel.id.replace('booking-step-', ''));
            window.setTimeout(() => invalid.reportValidity(), 0);
            return;
        }

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
