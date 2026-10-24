@props(['resource','id','label'=>'entry'])
@if(auth()->user()?->canManageEntries())
<div class="entry-actions">
    <button
        class="btn small hms-edit-entry"
        type="button"
        data-edit-url="{{ route('managed-entries.edit',[$resource,$id]) }}"
        data-update-url="{{ route('managed-entries.update',[$resource,$id]) }}"
        data-label="{{ $label }}"
        title="Edit {{ $label }}"
    >
        <i class="fas fa-pen-to-square"></i> Edit
    </button>

    <button
        class="btn small danger hms-delete-entry"
        type="button"
        data-delete-url="{{ route('managed-entries.destroy',[$resource,$id]) }}"
        data-label="{{ $label }}"
        title="Delete {{ $label }}"
    >
        <i class="fas fa-trash-can"></i> Delete
    </button>
</div>
@endif
