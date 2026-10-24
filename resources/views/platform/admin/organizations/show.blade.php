@extends('platform.layout')

@section('content')
    @component('platform.admin.organizations._card')
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">ID</label>
                <div class="col-sm-10">
                    <p class="form-control-plaintext">
                        {{ $organization->getId() }}
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-10">
                    <p class="form-control-plaintext">
                        {{ $organization->getName() }}
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Prefix</label>
                <div class="col-sm-10">
                    <p class="form-control-plaintext">
                        {{ $organization->getPrefix() ?: '—' }}
                    </p>
                </div>
            </div>
        </div>
    @endcomponent

    <div class="card mt-4">
        <div class="card-header">
            <span class="lead">Members</span>
        </div>

        @if(!empty($organizationUsers))
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th>CID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                    @foreach($organizationUsers as $organizationUser)
                        <tr>
                            <td>{{ $organizationUser->getUser()->getId() }}</td>
                            <td>{{ $organizationUser->getUser()->getFullName() }}</td>
                            <td>{{ $organizationUser->getUser()->getEmail() ?: '—' }}</td>
                            <td>{{ $organizationUser->getRoleName() }}</td>
                            <td>
                                <delete-resource link-only
                                                 route="{{ route('platform.admin.organizations.users.destroy', [$organization, $organizationUser->getUser()]) }}"
                                                 message="Are you sure you want to remove this user from the organization?">
                                    Remove
                                </delete-resource>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @else
            <div class="card-body text-center">
                <span>Nothing to show.</span>
            </div>
        @endif
    </div>

    @include('platform.organizations._add-user', [
        'action' => route('platform.admin.organizations.users.store', $organization),
        'cardClass' => 'mt-4',
    ])

    <div class="card mt-4">
        <div class="card-header">
            <span class="lead">URLs</span>
        </div>

        @if($urls->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>URL</th>
                        <th>Redirect URL</th>
                        <th>Last Updated</th>
                        <th></th>
                    </tr>
                    @foreach($urls as $url)
                        <tr>
                            <td>{{ $url->getId() }}</td>
                            <td class="break-all"><a href="{{ url($url->getFullUrl()) }}">{{ preg_replace('#^https?://#', '', $url->getFullUrl()) }}</a></td>
                            <td class="break-all"><a href="{{ $url->getRedirectUrl() }}">{{ preg_replace('#^https?://#', '', $url->getRedirectUrl()) }}</a></td>
                            <td>{{ hyphen_nobreak($url->getUpdatedAt()) }}</td>
                            <td><a href="{{ route('platform.admin.urls.edit', $url) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div class="mx-auto">{{ $urls->links() }}</div>
        @else
            <div class="card-body text-center">
                <span>Nothing to show.</span>
            </div>
        @endif
    </div>
@endsection
