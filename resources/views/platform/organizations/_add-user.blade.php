<div class="card {{ $cardClass ?? 'mb-4' }}">
    <div class="card-header"><span class="lead">Add User</span></div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}">
            {{ csrf_field() }}

            <div class="row mb-3">
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
                    <div class="form-text">
                        The user's VATSIM CID.
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="role_id" class="col-sm-2 col-form-label">Role</label>
                <div class="col-sm-10">
                    <select id="role_id" name="role_id"
                            class="form-select{{ $errors->has('role_id') ? ' is-invalid' : '' }}">
                        <option value="{{ \App\Entities\OrganizationUser::ROLE_OWNER }}"
                                {{ (old('role_id') ?: \App\Entities\OrganizationUser::ROLE_MEMBER) == \App\Entities\OrganizationUser::ROLE_OWNER ? 'selected' : '' }}>
                            Owner
                        </option>
                        <option value="{{ \App\Entities\OrganizationUser::ROLE_MANAGER }}"
                                {{ (old('role_id') ?: \App\Entities\OrganizationUser::ROLE_MEMBER) == \App\Entities\OrganizationUser::ROLE_MANAGER ? 'selected' : '' }}>
                            Manager
                        </option>
                        <option value="{{ \App\Entities\OrganizationUser::ROLE_MEMBER }}"
                                {{ (old('role_id') ?: \App\Entities\OrganizationUser::ROLE_MEMBER) == \App\Entities\OrganizationUser::ROLE_MEMBER ? 'selected' : '' }}>
                            Member
                        </option>
                    </select>
                    @if ($errors->has('role_id'))
                        <div class="invalid-feedback">
                            {{ $errors->first('role_id') }}
                        </div>
                    @endif
                    <div class="form-text">
                        Members can modify where URLs redirect to. Managers can transfer URL ownership and delete
                        URLs. Owners manage the organization and its members.
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </form>
    </div>
</div>
