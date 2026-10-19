@if($organizations->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>@sortablelink('prefix', 'Prefix', null, ['class' => 'text-nowrap'])</th>
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

    @if(!Request::has('sort'))
        <div class="mx-auto">{{ $organizations->links() }}</div>
    @else
        <div class="mx-auto">
            {{ $organizations->appends(['sort' => Request::get('sort'), 'direction' => Request::get('direction')])->links() }}
        </div>
    @endif
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
