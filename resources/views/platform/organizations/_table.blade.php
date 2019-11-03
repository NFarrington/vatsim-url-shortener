@if($organizations->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr class="link-unstyled">
                <th>@sortablelink('id', 'ID', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('name', 'Name', null, ['class' => 'text-nowrap'])</th>
                <th>Owners</th>
                <th>Managers</th>
                <th>Members</th>
                <th>@sortablelink('updated_at', 'Last Updated', null, ['class' => 'text-nowrap'])</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($organizations as $organization)
                <tr>
                    <td>{{ $organization->id }}</td>
                    <td class="break-all">{{ $organization->name }}</td>
                    <td>
                        <ul class="list-unstyled">
                            @forelse($organization->owners as $owner)
                                <li>
                                    <span title="{{ $owner->full_name }} ({{ $owner->id }})" class="text-limit">
                                        {{ $owner->full_name }} ({{ $owner->id }})
                                    </span>
                                </li>
                            @empty
                                <li>
                                    &mdash;
                                </li>
                            @endforelse
                        </ul>
                    </td>
                    <td>
                        <ul class="list-unstyled">
                            @forelse($organization->managers as $manager)
                                <li>
                                    <span title="{{ $manager->full_name }} ({{ $manager->id }})" class="text-limit">
                                        {{ $manager->full_name }} ({{ $manager->id }})
                                    </span>
                                </li>
                            @empty
                                <li>
                                    &mdash;
                                </li>
                            @endforelse
                        </ul>
                    </td>
                    <td>
                        <ul class="list-unstyled">
                            @forelse($organization->members as $member)
                                <li>
                                    <span title="{{ $member->full_name }} ({{ $member->id }})" class="text-limit">
                                        {{ $member->full_name }} ({{ $member->id }})
                                    </span>
                                </li>
                            @empty
                                <li>
                                    &mdash;
                                </li>
                            @endforelse
                        </ul>
                    </td>
                    <td>{{ hyphen_nobreak($organization->updated_at) }}</td>
                    <td>
                        @can('act-as-owner', $organization)
                            <a href="{{ route('platform.organizations.edit', $organization) }}">Edit</a>
                        @else
                            <s class="text-muted">Edit</s>
                        @endcan
                    </td>
                    <td>
                        @can('act-as-owner', $organization)
                            <delete-resource link-only
                                             route="{{ route('platform.organizations.destroy', $organization) }}"></delete-resource>
                        @else
                            <s class="text-muted">Delete</s>
                        @endcan
                    </td>
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
