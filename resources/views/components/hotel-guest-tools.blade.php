@php
    use App\Support\HotelFeatureSetting;

    $accessibilityEnabled = HotelFeatureSetting::bool(
        'accessibility.enabled',
        true
    );

    $readAloudEnabled = HotelFeatureSetting::bool(
        'accessibility.read_aloud',
        true
    );

    $chatbotEnabled = HotelFeatureSetting::bool(
        'chatbot.enabled',
        true
    );

    $chatbotTitle = HotelFeatureSetting::get(
        'chatbot.title',
        'Hotel Assistant'
    );

    $chatbotWelcome = HotelFeatureSetting::get(
        'chatbot.welcome',
        'Hello. How can I help with your stay?'
    );
@endphp

<div class="hotel-floating-tools">
    @if($accessibilityEnabled)
        <button
            type="button"
            class="hotel-floating-button"
            id="hotelAccessibilityToggle"
            aria-label="Open accessibility tools"
            title="Accessibility"
        >
            <i class="fas fa-universal-access"></i>
        </button>

        <div
            class="hotel-tool-panel"
            id="hotelAccessibilityPanel"
            hidden
        >
            <div class="hotel-tool-head">
                <strong>Accessibility</strong>

                <button
                    type="button"
                    data-close-tool="hotelAccessibilityPanel"
                    aria-label="Close"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <button type="button" data-access-action="font-up">
                <i class="fas fa-plus"></i>
                Increase text
            </button>

            <button type="button" data-access-action="font-down">
                <i class="fas fa-minus"></i>
                Decrease text
            </button>

            <button type="button" data-access-action="contrast">
                <i class="fas fa-circle-half-stroke"></i>
                High contrast
            </button>

            <button type="button" data-access-action="grayscale">
                <i class="fas fa-circle"></i>
                Greyscale
            </button>

            <button type="button" data-access-action="links">
                <i class="fas fa-link"></i>
                Underline links
            </button>

            @if($readAloudEnabled)
                <button type="button" data-access-action="read">
                    <i class="fas fa-volume-high"></i>
                    Read page
                </button>

                <button type="button" data-access-action="stop-read">
                    <i class="fas fa-stop"></i>
                    Stop reading
                </button>
            @endif

            <button type="button" data-access-action="reset">
                <i class="fas fa-rotate-left"></i>
                Reset
            </button>
        </div>
    @endif

    @if($chatbotEnabled)
        <button
            type="button"
            class="hotel-floating-button chatbot-button"
            id="hotelChatbotToggle"
            aria-label="Open hotel assistant"
            title="{{ $chatbotTitle }}"
        >
            <i class="fas fa-comments"></i>
        </button>

        <section
            class="hotel-chatbot-panel"
            id="hotelChatbotPanel"
            hidden
        >
            <header>
                <div>
                    <strong>{{ $chatbotTitle }}</strong>
                    <small>Guest assistance</small>
                </div>

                <button
                    type="button"
                    data-close-tool="hotelChatbotPanel"
                    aria-label="Close"
                >
                    <i class="fas fa-times"></i>
                </button>
            </header>

            <div class="hotel-chatbot-messages" id="hotelChatbotMessages">
                <div class="chat-message bot">
                    {{ $chatbotWelcome }}
                </div>
            </div>

            <form id="hotelChatbotForm">
                @csrf

                <input
                    type="text"
                    name="message"
                    id="hotelChatbotInput"
                    maxlength="1000"
                    placeholder="Ask about rooms, bookings or services..."
                    autocomplete="off"
                    required
                >

                <button type="submit" aria-label="Send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </section>
    @endif
</div>

<link
    rel="stylesheet"
    href="{{ asset('css/hotel-guest-tools.css') }}?v={{ @filemtime(public_path('css/hotel-guest-tools.css')) ?: time() }}"
>

<script>
window.hotelGuestTools = {
    chatbotUrl: @json(route('public.chatbot.message')),
    csrf: @json(csrf_token()),
    defaultFontScale: @json(
        (int) HotelFeatureSetting::get(
            'accessibility.default_font_scale',
            100
        )
    )
};
</script>

<script
    src="{{ asset('js/hotel-guest-tools.js') }}?v={{ @filemtime(public_path('js/hotel-guest-tools.js')) ?: time() }}"
    defer
></script>
