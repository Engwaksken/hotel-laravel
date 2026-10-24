@extends('layouts.app')

@section('title', 'Permissions')
@section('page_title', 'Roles & Permissions')

@section('content')
<div class="card">
    <h3>Role Permissions</h3>
    <form method="POST" action="{{ route('management.permissions.role') }}">
        @csrf
        <label>
            Role
            <select name="role">
                @foreach([
                    'super_admin','hotel_owner','general_manager','branch_manager',
                    'front_office_manager','accountant','receptionist','cashier',
                    'restaurant_manager','waiter','chef','bar_manager','bartender',
                    'store_manager','procurement_officer','housekeeping_manager',
                    'housekeeper','maintenance','hr','auditor'
                ] as $role)
                    <option value="{{ $role }}">{{ ucwords(str_replace('_',' ',$role)) }}</option>
                @endforeach
            </select>
        </label>

        <div class="grid-3" style="margin:12px 0">
            @foreach($permissions as $permission)
                <label class="check">
                    <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}">
                    {{ $permission->module }} · {{ $permission->label }}
                </label>
            @endforeach
        </div>

        <button class="btn primary"><i class="fas fa-floppy-disk"></i> Save Role Permissions</button>
    </form>
</div>

<div class="card">
    <h3>Users</h3>
    <p>{{ $users->count() }} user accounts available for per-user overrides.</p>
</div>
@endsection
