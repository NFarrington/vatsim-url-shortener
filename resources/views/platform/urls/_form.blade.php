<div class="form-group row">
    <label for="inputUrl" class="col-sm-2 col-form-label">Short URL</label>
    @if (!$newUrl)
        <div class="col-sm-10">
            <input type="text" class="form-control" id="inputUrl" disabled
                   value="{{ $url->getDomain()->getUrl() }}{{ $url->getPrefix() ? $url->getOrganization()->getPrefix().'/' : '' }}{{ $url->getUrl() }}">
        </div>
    @else
        <div class="col-sm-10 form-row">
            <div class="col-auto">
                <select name="domain_id" class="custom-select{{ $errors->has('domain_id') ? ' is-invalid' : '' }}"
                        autofocus>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->getId() }}" {{ old('domain_id') == $domain->getId() ? 'selected' : '' }}>
                            {{ $domain->getUrl() }}
                        </option>
                    @endforeach
                </select>
                @if ($errors->has('domain_id'))
                    <div class="invalid-feedback">
                        {{ $errors->first('domain_id') }}
                    </div>
                @endif
            </div>
            @if (!empty($prefixes))
                <div class="col-auto">
                    <select name="prefix" class="custom-select{{ $errors->has('prefix') ? ' is-invalid' : '' }}">
                        <option value="" {{ old('prefix') == '' ? 'selected' : '' }}></option>
                        @foreach ($prefixes as $prefix)
                            <option value="{{ $prefix }}" {{ old('prefix') == $prefix ? 'selected' : '' }}>
                                /{{ $prefix }}/
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('prefix'))
                        <div class="invalid-feedback">
                            {{ $errors->first('prefix') }}
                        </div>
                    @endif
                </div>
            @endif
            <div class="col">
                <input type="text" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}"
                       id="inputUrl" name="url" value="{{ old('url') ?: $url->getUrl() }}"
                       placeholder="my-short-url" maxlength="30" required {{ !$newUrl ? 'disabled' : '' }}>
                @if ($errors->has('url'))
                    <div class="invalid-feedback">
                        {{ $errors->first('url') }}
                    </div>
                @endif
                <small class="form-text text-muted">
                    The short form of the URL. Leave blank to have one automatically generated.
                </small>
            </div>
        </div>
    @endif
</div>

<div class="form-group row">
    <label for="inputRedirectUrl" class="col-sm-2 col-form-label">Redirect URL</label>
    <div class="col-sm-10">
        <input type="text" class="form-control{{ $errors->has('redirect_url') ? ' is-invalid' : '' }}"
               id="inputRedirectUrl" name="redirect_url" value="{{ old('redirect_url') ?: $url->getRedirectUrl() }}"
               placeholder="https://example.com/redirect-here" required maxlength="1000">
        @if ($errors->has('redirect_url'))
            <div class="invalid-feedback">
                {{ $errors->first('redirect_url') }}
            </div>
        @endif
        <small class="form-text text-muted">
            The URL you want to redirect to.
        </small>
    </div>
</div>

<div class="form-group row">
    <label for="inputOrganizationId" class="col-sm-2 col-form-label">Organization</label>
    <div class="col-sm-10">
        @if(!$newUrl && !Gate::check('move', $url))
            <select id="inputOrganizationId" disabled
                    class="custom-select {{ $errors->has('organization_id') ? 'is-invalid' : '' }}">
                <option value="">{{ $url->getOrganization()->getName() ?? 'None' }}</option>
            </select>
            <input type="hidden" name="organization_id" value="{{ $url->getOrganization()->getId() }}">
        @else
            <select id="inputOrganizationId" name="organization_id"
                    class="custom-select {{ $errors->has('organization_id') ? 'is-invalid' : '' }}">
                <option value="">None</option>
                @foreach($organizations as $organization)
                    <option value="{{ $organization->getId() }}"
                            {{ (old('organization_id') ?: ($url->getOrganization() ? $url->getOrganization()->getId() : null)) == $organization->getId() ? 'selected' : '' }}>
                        {{ $organization->getName() }}
                    </option>
                @endforeach
            </select>
        @endif
        @if ($errors->has('organization_id'))
            <div class="invalid-feedback">
                {{ $errors->first('organization_id') }}
            </div>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="offset-sm-2 col-sm-10">
        <p><i class="material-icons md-18 text-danger">warning</i> All URLs and click analytics are public and can be
            accessed by anyone.</p>
    </div>
</div>
