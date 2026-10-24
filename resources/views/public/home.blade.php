<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $web->property->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/public-hotel.css') }}">
</head>
<body>
<header class="site-nav">
    <strong>{{ $web->property->name }}</strong>
    <nav aria-label="Hotel navigation">
        <a href="#rooms">Rooms</a>
        <a href="#offers">Offers</a>
        <a href="#contact">Contact</a>
        @if($web->online_booking_enabled)
            <a class="btn" href="{{ route('public.booking', $web->slug) }}">Book now</a>
        @endif
    </nav>
</header>

<section class="hero" @if($web->hero_image) style="background-image:linear-gradient(#0008,#0008),url('{{ asset($web->hero_image) }}')" @endif>
    <div>
        <span>Welcome to Uganda</span>
        <h1>{{ $web->headline ?: $web->property->name }}</h1>
        <p>{{ $web->intro ?: 'Comfortable stays, warm hospitality and effortless booking.' }}</p>
        @if($web->online_booking_enabled)
            <a class="btn" href="{{ route('public.booking', $web->slug) }}">Check availability</a>
        @else
            <a class="btn" href="#contact">Enquire</a>
        @endif
    </div>
</section>

<main>
    <section id="rooms">
        <h2>Rooms &amp; Suites</h2>
        @if($types->isNotEmpty())
            <div class="grid">
                @foreach($types as $type)
                    <article class="card room-card">
                        <h3>{{ $type->name }}</h3>
                        @if($type->description)
                            <p>{{ $type->description }}</p>
                        @endif
                        <p><b>UGX {{ number_format((float) $type->base_rate) }}</b> / night</p>
                        <small>Up to {{ $type->max_adults }} adults{{ $type->max_children ? ', '.$type->max_children.' children' : '' }}</small>
                        @if($web->online_booking_enabled)
                            <a class="btn room-book-btn" href="{{ route('public.booking', ['slug' => $web->slug, 'room_type_id' => $type->id]) }}">Book this room</a>
                        @else
                            <a class="btn room-book-btn" href="#contact">Enquire about this room</a>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="empty">Room options will be published here soon. Please contact the hotel for assistance.</p>
        @endif
    </section>

    <section id="offers">
        <h2>Special offers</h2>
        <div class="grid">
            @forelse($promos as $promo)
                <article class="card">
                    <h3>{{ $promo->name }}</h3>
                    <p>{{ $promo->description }}</p>
                    @if($promo->code)<b>Code: {{ $promo->code }}</b>@endif
                </article>
            @empty
                <p>Contact the hotel for current packages and offers.</p>
            @endforelse
        </div>
    </section>

    <section id="contact">
        <h2>Contact</h2>
        @if($web->address)<p>{{ $web->address }}</p>@endif
        @if($web->contact_phone)<p><a href="tel:{{ $web->contact_phone }}">{{ $web->contact_phone }}</a></p>@endif
        @if($web->contact_email)<p><a href="mailto:{{ $web->contact_email }}">{{ $web->contact_email }}</a></p>@endif
    </section>
</main>

<footer class="public-hotel-footer">&copy; {{ date('Y') }} {{ $web->property->name }}</footer>
</body>
</html>
