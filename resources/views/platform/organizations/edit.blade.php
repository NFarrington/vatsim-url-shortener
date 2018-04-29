@extends('platform.layout')

@section('content')
    @component('platform.organizations._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.organizations.update', $organization) }}">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                @include('platform.organizations._form-edit')
            </form>
        </div>
    @endcomponent

    <div class="card mb-4">
        <div class="card-header"><span class="lead">Add User</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('platform.organizations.users.store', $organization) }}">
                {{ csrf_field() }}

                <div class="form-group row">
                    <label for="inputId" class="col-sm-2 col-form-label">CID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control{{ $errors->has('id') ? ' is-invalid' : '' }}"
                               id="inputId" name="id" value="{{ old('id') }}"
                               placeholder="CID" required>
                        @if ($errors->has('id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('id') }}
                            </div>
                        @endif
                        <small class="form-text text-muted">
                            The user's VATSIM CID.
                        </small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="role_id" class="col-sm-2 col-form-label">Role</label>
                    <div class="col-sm-10">
                        <select id="role_id" name="role_id"
                                class="custom-select{{ $errors->has('role_id') ? ' is-invalid' : '' }}">
                            <option value="{{ \App\Models\OrganizationUser::ROLE_OWNER }}"
                                    {{ (old('role_id') ?: \App\Models\OrganizationUser::ROLE_MEMBER) == \App\Models\OrganizationUser::ROLE_OWNER ? 'selected' : '' }}>
                                Owner
                            </option>
                            <option value="{{ \App\Models\OrganizationUser::ROLE_MANAGER }}"
                                    {{ (old('role_id') ?: \App\Models\OrganizationUser::ROLE_MEMBER) == \App\Models\OrganizationUser::ROLE_MANAGER ? 'selected' : '' }}>
                                Manager
                            </option>
                            <option value="{{ \App\Models\OrganizationUser::ROLE_MEMBER }}"
                                    {{ (old('role_id') ?: \App\Models\OrganizationUser::ROLE_MEMBER) == \App\Models\OrganizationUser::ROLE_MEMBER ? 'selected' : '' }}>
                                Member
                            </option>
                        </select>
                        @if ($errors->has('role_id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('role_id') }}
                            </div>
                        @endif
                        <small class="form-text text-muted">
                            Members can modify where URLs redirect to. Managers can transfer URL ownership and delete
                            URLs. Owners manage the organization and its members.
                        </small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
