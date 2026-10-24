@extends('platform.layout')

@section('content')
    @component('platform.admin.urls._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.admin.urls.update', $url) }}">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="row mb-3">
                    <label for="inputUrl" class="col-sm-2 col-form-label">Short URL</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputUrl" disabled value="{{ $url->getFullUrl() }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputRedirectUrl" class="col-sm-2 col-form-label">Redirect URL</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control{{ $errors->has('redirect_url') ? ' is-invalid' : '' }}"
                               id="inputRedirectUrl" name="redirect_url"
                               value="{{ old('redirect_url') ?: $url->getRedirectUrl() }}"
                               placeholder="https://example.com/redirect-here" required maxlength="1000">
                        @if ($errors->has('redirect_url'))
                            <div class="invalid-feedback">
                                {{ $errors->first('redirect_url') }}
                            </div>
                        @endif
                    </div>
                </div>

                @can('transfer', $url)
                    @php($selectedOwnerType = old('owner_type', $ownerType))
                    <fieldset class="row mb-3">
                        <legend class="col-sm-2 col-form-label pt-0">Owner</legend>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input{{ $errors->has('owner_type') ? ' is-invalid' : '' }}"
                                       type="radio" name="owner_type" id="inputOwnerTypeNone" value="none"
                                        {{ $selectedOwnerType === 'none' ? 'checked' : '' }}>
                                <label class="form-check-label" for="inputOwnerTypeNone">
                                    Nobody (listed as a public URL)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input{{ $errors->has('owner_type') ? ' is-invalid' : '' }}"
                                       type="radio" name="owner_type" id="inputOwnerTypeUser" value="user"
                                        {{ $selectedOwnerType === 'user' ? 'checked' : '' }}>
                                <label class="form-check-label" for="inputOwnerTypeUser">User</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input{{ $errors->has('owner_type') ? ' is-invalid' : '' }}"
                                       type="radio" name="owner_type" id="inputOwnerTypeOrganization" value="organization"
                                        {{ $selectedOwnerType === 'organization' ? 'checked' : '' }}>
                                <label class="form-check-label" for="inputOwnerTypeOrganization">Organization</label>
                                @if ($errors->has('owner_type'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('owner_type') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    <div class="row mb-3">
                        <label for="inputUserId" class="col-sm-2 col-form-label">User CID</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control{{ $errors->has('user_id') ? ' is-invalid' : '' }}"
                                   id="inputUserId" name="user_id" placeholder="CID"
                                   value="{{ old('user_id', $ownerType === 'user' ? $url->getUser()->getId() : '') }}">
                            @if ($errors->has('user_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('user_id') }}
                                </div>
                            @endif
                            <div class="form-text">
                                Used when the owner is a user. Users who haven't registered are added from VATSIM's
                                records.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputOrganizationId" class="col-sm-2 col-form-label">Organization</label>
                        <div class="col-sm-10">
                            @php($selectedOrganizationId = old('organization_id', $ownerType === 'organization' ? $url->getOrganization()->getId() : ''))
                            <select id="inputOrganizationId" name="organization_id"
                                    class="form-select{{ $errors->has('organization_id') ? ' is-invalid' : '' }}">
                                <option value=""></option>
                                @foreach($organizations as $organization)
                                    <option value="{{ $organization->getId() }}"
                                            {{ $selectedOrganizationId == $organization->getId() ? 'selected' : '' }}>
                                        {{ $organization->getName() }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('organization_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('organization_id') }}
                                </div>
                            @endif
                            <div class="form-text">
                                Used when the owner is an organization.
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Owner</label>
                        <div class="col-sm-10">
                            <p class="form-control-plaintext">
                                @if($ownerType === 'organization')
                                    <a href="{{ route('platform.admin.organizations.show', $url->getOrganization()) }}">{{ $url->getOrganization()->getName() }}</a>
                                @elseif($ownerType === 'user')
                                    {{ $url->getUser()->getDisplayInfo() }}
                                @else
                                    Nobody
                                @endif
                            </p>
                            <div class="form-text">
                                {{ $url->isPrefixed() ? 'Prefixed URLs' : 'URLs on restricted domains' }} can't be transferred.
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                    <input type="hidden" name="user_id" value="{{ $url->getUser()?->getId() }}">
                    <input type="hidden" name="organization_id" value="{{ $url->getOrganization()?->getId() }}">
                @endcan

                <div class="row mb-3">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="offset-sm-2 col-sm-10">
                    <delete-resource route="{{ route('platform.admin.urls.destroy', $url) }}"
                                     message="Are you sure you want to delete this URL?">Delete URL</delete-resource>
                </div>
            </div>
        </div>
    @endcomponent
@endsection
