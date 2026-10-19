@if($organizations->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Prefix</th>
                <th>Members</th>
                <th></th>
            </tr>
            @foreach($organizations as $organization)
                <tr>
                    <td>{{ $organization->getId() }}</td>
                    <td>{{ $organization->getName() }}</td>
                    <td>{{ $organization->getPrefix() ?: '—' }}</td>
                    <td>{{ $memberCounts[$organization->getId()] ?? 0 }}</td>
                    <td><a href="{{ route('platform.admin.organizations.show', $organization) }}">View</a></td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-auto">{{ $organizations->links() }}</div>
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
