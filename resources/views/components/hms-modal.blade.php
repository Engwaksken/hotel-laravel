@props([
    'id',
    'title',
    'size' => 'md',
    'closeOnBackdrop' => true,
])

<div
    class="modal hms-modal"
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    data-hms-modal
    data-close-backdrop="{{ $closeOnBackdrop ? '1' : '0' }}"
>
    <div
        class="modal-box hms-modal-box hms-modal-{{ $size }}"
        role="document"
    >
        <button
            type="button"
            class="modal-close"
            data-modal-close
            aria-label="Close"
        >
            <i class="fas fa-xmark"></i>
        </button>

        <div class="hms-modal-header">
            <h2 id="{{ $id }}-title">
                {{ $title }}
            </h2>
        </div>

        <div class="hms-modal-body">
            {{ $slot }}
        </div>
    </div>
</div>
