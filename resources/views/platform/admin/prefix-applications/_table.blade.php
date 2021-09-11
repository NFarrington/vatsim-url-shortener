@if($prefixApplications->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>Organization</th>
                <th>Prefix</th>
                <th>Applicant ID</th>
                <th>Applicant Name</th>
                <th></th>
            </tr>
            @foreach($prefixApplications as $prefixApplication)
                <tr>
                    <td>{{ $prefixApplication->getId() }}</td>
                    <td>{{ $prefixApplication->getOrganization()->getName() }}</td>
                    <td>{{ $prefixApplication->getPrefix() }}</td>
                    <td>{{ $prefixApplication->getUser()->getId() }}</td>
                    <td>{{ $prefixApplication->getUser()->getFullName() }}</td>
                    <td><a href="{{ route('platform.admin.prefix-applications.edit', $prefixApplication) }}">Review</a></td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-auto">{{ $prefixApplications->links() }}</div>
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
