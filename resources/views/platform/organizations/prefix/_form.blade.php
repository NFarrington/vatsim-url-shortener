<div class="form-group row">
    <div class="offset-2 col-sm-10">
        <div class="alert alert-danger">
            <p class="form-control-plaintext">
                Only official VATSIM entities are eligible for a prefix (e.g. regions, divisions, and sub-divisions).
                Only the recognised head of the organisation (e.g. a director) should submit the request for a prefix.
            </p>
        </div>
        <div class="alert alert-primary">
            <p class="form-control-plaintext">
                A prefix provides organizations with a guaranteed way of using any short URL with their prefix, and to
                help users identify the locale a link is pointing to. For example, it would make more sense to use
                https://vats.im/uk/forums to point to the VATSIM UK forums, rather than just https://vats.im/forums,
                which could belong to any organization.
            </p>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Organization</label>
    <div class="col-sm-10">
        <p class="form-control-plaintext">
            {{ $organization->name }}
        </p>
    </div>
</div>

<div class="form-group row">
    <label for="inputIdentityUrl" class="col-sm-2 col-form-label">Identity Verification URL</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('identity_url') ? ' is-invalid' : '' }}"
               id="inputIdentityUrl" name="identity_url"
               value="{{ old('identity_url') ?: $organization->identity_url }}"
               placeholder="Identity verification URL" required maxlength="50" autofocus>
        @if ($errors->has('identity_url'))
            <div class="invalid-feedback">
                {{ $errors->first('identity_url') }}
            </div>
        @endif
        <small class="form-text text-muted">
            This must link to a page on your organization's official website which positively identifies you as the head
            of the organization.
        </small>
    </div>
</div>

<div class="form-group row">
    <label for="inputName" class="col-sm-2 col-form-label">Requested Prefix</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('prefix') ? ' is-invalid' : '' }}"
               id="inputName" name="prefix" value="{{ old('prefix') ?: $organization->prefix }}"
               placeholder="Prefix" required maxlength="50" autofocus>
        @if ($errors->has('prefix'))
            <div class="invalid-feedback">
                {{ $errors->first('prefix') }}
            </div>
        @endif
        <small class="form-text text-muted">
            This should typically be your organization's VATSIM 'code', e.g. VATEUR would request the prefix 'eur'. This
            may be changed to a more suitable value before your prefix application is approved.
        </small>
    </div>
</div>
