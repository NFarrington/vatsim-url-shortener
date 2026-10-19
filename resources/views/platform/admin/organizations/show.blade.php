@extends('platform.layout')

@section('content')
    @component('platform.admin.organizations._card')
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">ID</label>
                <div class="col-sm-10">
                    <p class="form-control-plaintext">
                        {{ $organization->getId() }}
                    </p>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-10">
                    <p class="form-control-plaintext">
                        {{ $organization->getName() }}
                    </p>
                </div>
            </div>

            <div class="form-group row">
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
                    </tr>
                    @foreach($organizationUsers as $organizationUser)
                        <tr>
                            <td>{{ $organizationUser->getUser()->getId() }}</td>
                            <td>{{ $organizationUser->getUser()->getFullName() }}</td>
                            <td>{{ $organizationUser->getUser()->getEmail() ?: '—' }}</td>
                            <td>{{ $organizationUser->getRoleName() }}</td>
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
@endsection
