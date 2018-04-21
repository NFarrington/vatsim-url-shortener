<div class="form-group row">
    <label for="inputName" class="col-sm-2 col-form-label">Name</label>
    <div class="col-sm-10 form-row">
        <div class="col-auto">
            <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                   id="inputName" name="name" value="{{ old('name') ?: $organization->name }}"
                   placeholder="Name" required maxlength="50" autofocus>
            @if ($errors->has('name'))
                <div class="invalid-feedback">
                    {{ $errors->first('name') }}
                </div>
            @endif
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Managers</label>
    @forelse($organization->managers as $manager)
        <div class="{{ !$loop->first ? 'offset-sm-2' : '' }} col-sm-10">
            <p class="form-control-plaintext">
                {{ $manager->display_info }}
                <delete-resource link-only
                                 route="{{ route('platform.organizations.users.destroy', [$organization, $manager]) }}">
                    (Remove)
                </delete-resource>
            </p>
        </div>
    @empty
        <div class="col-sm-10">
            <input type="text" readonly class="form-control-plaintext" value="&mdash;">
        </div>
    @endforelse
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Members</label>
    @forelse($organization->members as $member)
        <div class="{{ !$loop->first ? 'offset-sm-2' : '' }} col-sm-10">
            <p class="form-control-plaintext">
                {{ $member->display_info }}
                <delete-resource link-only
                                 route="{{ route('platform.organizations.users.destroy', [$organization, $member]) }}">
                    (Remove)
                </delete-resource>
            </p>
        </div>
    @empty
        <div class="col-sm-10">
            <input type="text" readonly class="form-control-plaintext" value="&mdash;">
        </div>
    @endforelse
</div>
