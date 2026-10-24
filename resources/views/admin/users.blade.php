@extends('layouts.app')

@section('title', 'Users & Roles')
@section('page_title', 'Users & Roles')

@section('content')
<div class="page-head">
    <div>
        <h1>Branch users</h1>
        <p>Manage staff access for {{ $property->name ?? 'this hotel branch' }}.</p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2><i class="fas fa-user-plus"></i> Create user</h2>
        @if(auth()->user()->isBranchManager())
            <span class="badge info"><i class="fas fa-building"></i> Branch Manager access</span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="form-grid">
        @csrf

        <label>
            Full name
            <input name="name" value="{{ old('name') }}" required>
        </label>

        <label>
            Email
            <input name="email" type="email" value="{{ old('email') }}" required>
        </label>

        <label>
            Phone
            <input name="phone" value="{{ old('phone') }}">
        </label>

        <label>
            Role
            <select name="role" required>
                @foreach($allowedRoles as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>
                        {{ ucwords(str_replace('_', ' ', $role)) }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            Temporary password
            <input name="password" type="password" required autocomplete="new-password">
        </label>

        <label class="check" style="align-self:end;min-height:36px">
            <input type="checkbox" name="is_active" value="1" checked>
            Active account
        </label>

        <div class="span-2 actions end">
            <button class="btn primary">
                <i class="fas fa-user-check"></i> Create account
            </button>
        </div>
    </form>

    @if(auth()->user()->isBranchManager())
        <div class="alert warning" style="margin-top:12px;margin-bottom:0">
            <i class="fas fa-shield-halved"></i>
            Branch Managers can create operational staff for their assigned branch, but cannot create or promote Super Admins, Hotel Owners, General Managers or other Branch Managers.
        </div>
    @endif
</div>

<div class="card">
    <div class="card-head">
        <h2><i class="fas fa-users"></i> Branch staff</h2>
        <span class="badge">{{ $users->total() }} users</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><b>{{ $user->name }}</b></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?: '—' }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $user->role)) }}</td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No branch users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($users, 'links'))
        {{ $users->links() }}
    @endif
</div>
@endsection
