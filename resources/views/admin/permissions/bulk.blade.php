@extends('layouts.app')

@section('title', 'Role Permissions')
@section('page_title', 'Role Permissions')

@section('content')
<div class="permissions-page">
    <div class="page-head">
        <div>
            <span class="eyebrow">
                <i class="fas fa-user-shield"></i>
                Access Control
            </span>

            <h1>Role permissions</h1>

            <p>
                Select a role and assign multiple system permissions.
                Existing user role names remain unchanged.
            </p>
        </div>
    </div>

    @if($schemaWarning)
        <div class="alert warning">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>Permissions not installed</strong>
                <div>{{ $schemaWarning }}</div>
            </div>
        </div>
    @endif

    @if($roles->isNotEmpty())
        <div class="card">
            <form method="GET" action="{{ route('admin.permissions.bulk') }}">
                <div class="form-grid">
                    <div>
                        <label for="role_id">Role</label>
                        <select
                            id="role_id"
                            name="role_id"
                            onchange="this.form.submit()"
                        >
                            @foreach($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    @selected((int)$selectedRoleId === (int)$role->id)
                                >
                                    {{ $role->display_name ?? $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <form
            method="POST"
            action="{{ route('admin.permissions.bulk.sync') }}"
            class="card"
            style="margin-top:12px"
        >
            @csrf

            <input
                type="hidden"
                name="role_id"
                value="{{ $selectedRoleId }}"
            >

            <div class="toolbar">
                <div>
                    <strong>Permissions</strong>
                    <small>
                        <span id="selectedCount">
                            {{ $selectedPermissionIds->count() }}
                        </span>
                        selected
                    </small>
                </div>

                <div class="toolbar-actions">
                    <input
                        id="permissionSearch"
                        type="search"
                        placeholder="Search permissions"
                    >

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        id="selectVisible"
                    >
                        Select visible
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="clearAll"
                    >
                        Clear
                    </button>
                </div>
            </div>

            @foreach($permissions->groupBy(fn($permission) => $permission->module ?: 'general') as $module => $modulePermissions)
                <section class="permission-module">
                    <h3>
                        {{ ucwords(str_replace('_', ' ', $module)) }}
                        <span>{{ $modulePermissions->count() }}</span>
                    </h3>

                    <div class="permission-grid">
                        @foreach($modulePermissions as $permission)
                            @php
                                $label =
                                    $permission->display_name
                                    ?? $permission->name;
                            @endphp

                            <label
                                class="permission-option"
                                data-permission
                                data-search="{{ strtolower($label.' '.$permission->name.' '.$module) }}"
                            >
                                <input
                                    class="permission-check"
                                    type="checkbox"
                                    name="permission_ids[]"
                                    value="{{ $permission->id }}"
                                    @checked(
                                        $selectedPermissionIds
                                            ->contains((int)$permission->id)
                                    )
                                >

                                <span>
                                    <strong>{{ $label }}</strong>
                                    <small>{{ $permission->name }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="form-actions">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-floppy-disk"></i>
                    Save selected permissions
                </button>
            </div>
        </form>
    @endif
</div>
@endsection

@push('styles')
<style>
.permissions-page .page-head {
    margin-bottom: 14px;
}

.permissions-page .page-head h1 {
    margin: 4px 0 3px;
    font-size: 21px;
}

.permissions-page .page-head p {
    margin: 0;
    color: var(--hms-muted);
    font-size: 10px;
}

.permissions-page .eyebrow {
    color: var(--hms-primary);
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
}

.permissions-page .toolbar {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-bottom:14px;
}

.permissions-page .toolbar small {
    display:block;
    color:var(--hms-muted);
    margin-top:2px;
}

.permissions-page .toolbar-actions {
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:wrap;
}

.permissions-page .toolbar-actions input {
    min-width:220px;
}

.permission-module {
    margin-bottom:16px;
}

.permission-module h3 {
    margin:0 0 7px;
    display:flex;
    align-items:center;
    gap:7px;
    font-size:11px;
}

.permission-module h3 span {
    min-width:22px;
    height:22px;
    padding:0 6px;
    display:inline-grid;
    place-items:center;
    border-radius:999px;
    background:var(--hms-primary-soft);
    color:var(--hms-primary);
    font-size:8px;
}

.permission-grid {
    display:grid;
    grid-template-columns:
        repeat(auto-fill,minmax(210px,1fr));
    gap:7px;
}

.permission-option {
    min-width:0;
    margin:0 !important;
    padding:9px;
    display:flex !important;
    align-items:center;
    gap:8px;
    border:1px solid var(--hms-border);
    border-radius:8px;
    background:#fff;
    cursor:pointer;
}

.permission-option:has(input:checked) {
    border-color:rgba(15,118,110,.35);
    background:var(--hms-primary-soft);
}

.permission-option input {
    flex:0 0 15px;
    width:15px !important;
    height:15px !important;
    min-height:0 !important;
    margin:0;
    accent-color:var(--hms-primary);
}

.permission-option span {
    min-width:0;
}

.permission-option strong,
.permission-option small {
    display:block;
}

.permission-option strong {
    font-size:9px;
}

.permission-option small {
    margin-top:2px;
    color:var(--hms-muted);
    font-size:7.5px;
}

@media(max-width:680px) {
    .permissions-page .toolbar,
    .permissions-page .toolbar-actions {
        align-items:stretch;
        flex-direction:column;
    }

    .permissions-page .toolbar-actions input {
        min-width:100%;
        width:100%;
    }

    .permission-grid {
        grid-template-columns:1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checks = Array.from(
        document.querySelectorAll('.permission-check')
    );

    const items = Array.from(
        document.querySelectorAll('[data-permission]')
    );

    const count =
        document.getElementById('selectedCount');

    function updateCount() {
        if (count) {
            count.textContent =
                checks.filter(item => item.checked).length;
        }
    }

    checks.forEach(item =>
        item.addEventListener('change', updateCount)
    );

    document
        .getElementById('selectVisible')
        ?.addEventListener('click', function () {
            items
                .filter(item => !item.hidden)
                .forEach(item => {
                    const check =
                        item.querySelector('.permission-check');

                    if (check) {
                        check.checked = true;
                    }
                });

            updateCount();
        });

    document
        .getElementById('clearAll')
        ?.addEventListener('click', function () {
            checks.forEach(
                item => item.checked = false
            );

            updateCount();
        });

    document
        .getElementById('permissionSearch')
        ?.addEventListener('input', function (event) {
            const query =
                event.target.value
                    .trim()
                    .toLowerCase();

            items.forEach(function (item) {
                item.hidden =
                    query !== ''
                    && !(
                        item.dataset.search ?? ''
                    ).includes(query);
            });
        });

    updateCount();
});
</script>
@endpush
