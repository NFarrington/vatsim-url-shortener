<div class="form-group row">
    <label for="inputName" class="col-sm-2 col-form-label">Name</label>
    <div class="col-sm-10 form-row">
        <div class="col-auto">
            <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                   id="inputName" name="name" value="{{ old('name') ?: $organization->getName() }}"
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
    <label class="col-sm-2 col-form-label">Prefix</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            @if($organization->getPrefix())
                {{ config('app.url') }}/{{ $organization->getPrefix() }}/
            @elseif($organization->getPrefixApplication())
                Pending Approval since {{ $organization->getPrefixApplication()->getCreatedAt()->diffForHumansAt() }}
            @else
                None &mdash;
                <a href="{{ route('platform.organizations.prefix.create', $organization) }}">Apply Here</a>
            @endif
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Owners</label>
    @forelse($organization->getUsers(\App\Entities\OrganizationUser::ROLE_OWNER) as $owner)
        <div class="{{ !$loop->first ? 'offset-sm-2' : '' }} col-sm-10">
            <p class="form-control-plaintext">
                {{ $owner->getDisplayInfo() }}
                <delete-resource link-only
                                 route="{{ route('platform.organizations.users.destroy', [$organization, $owner]) }}">
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
    <label class="col-sm-2 col-form-label">Managers</label>
    @forelse($organization->getUsers(\App\Entities\OrganizationUser::ROLE_MANAGER) as $manager)
        <div class="{{ !$loop->first ? 'offset-sm-2' : '' }} col-sm-10">
            <p class="form-control-plaintext">
                {{ $manager->getDisplayInfo() }}
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
    @forelse($organization->getUsers(\App\Entities\OrganizationUser::ROLE_MEMBER) as $member)
        <div class="{{ !$loop->first ? 'offset-sm-2' : '' }} col-sm-10">
            <p class="form-control-plaintext">
                {{ $member->getDisplayInfo() }}
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
