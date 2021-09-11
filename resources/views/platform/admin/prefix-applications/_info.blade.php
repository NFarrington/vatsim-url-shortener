<div class="form-group row">
    <label class="col-sm-2 col-form-label">ID</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            {{ $prefixApplication->getId() }}
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Organization Name</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            {{ $prefixApplication->getOrganization()->getName() }}
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Identity URL</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            <a href="{{ $prefixApplication->getIdentityUrl() }}">{{ $prefixApplication->getIdentityUrl() }}</a>
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Requested Prefix</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            {{ $prefixApplication->getPrefix() }}
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Applicant ID</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            {{ $prefixApplication->getUser()->getId() }}
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Applicant Name</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            {{ $prefixApplication->getUser()->getFullName() }}
        </p>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Organization Users</label>
    @forelse($prefixApplication->getOrganization()->getOrganizationUsers() as $organizationUser)
    <div class="{{ !$loop->first ? 'offset-sm-2' : '' }} col-sm-10">
        <p class="form-control-plaintext">
            {{ $organizationUser->getUser()->getDisplayInfo() }} ({{ $organizationUser->getRoleName() }})
        </p>
    </div>
    @empty
    <div class="col-sm-10">
        <input type="text" readonly class="form-control-plaintext" value="&mdash;">
    </div>
    @endforelse
</div>
