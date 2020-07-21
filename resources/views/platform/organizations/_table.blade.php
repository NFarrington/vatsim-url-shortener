@if($organizations->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr class="link-unstyled">
                <th>@sortablelink('id', 'ID', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('name', 'Name', null, ['class' => 'text-nowrap'])</th>
                <th>Owners</th>
                <th>Managers</th>
                <th>Members</th>
                <th>@sortablelink('updatedAt', 'Last Updated', null, ['class' => 'text-nowrap'])</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($organizations as $organization)
                <tr>
                    <td>{{ $organization->getId() }}</td>
                    <td class="break-all">{{ $organization->getName() }}</td>
                    <td>
                        <ul class="list-unstyled">
                            @forelse($organization->getUsers(\App\Entities\OrganizationUser::ROLE_OWNER) as $owner)
                                <li>
                                    <span title="{{ $owner->getFullName() }} ({{ $owner->getId() }})" class="text-limit">
                                        {{ $owner->getFullName() }} ({{ $owner->getId() }})
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
                            @forelse($organization->getUsers(\App\Entities\OrganizationUser::ROLE_MANAGER) as $manager)
                                <li>
                                    <span title="{{ $manager->getFullName() }} ({{ $manager->getId() }})" class="text-limit">
                                        {{ $manager->getFullName() }} ({{ $manager->getId() }})
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
                            @forelse($organization->getUsers(\App\Entities\OrganizationUser::ROLE_MEMBER) as $member)
                                <li>
                                    <span title="{{ $member->getFullName() }} ({{ $member->getId() }})" class="text-limit">
                                        {{ $member->getFullName() }} ({{ $member->getId() }})
                                    </span>
                                </li>
                            @empty
                                <li>
                                    &mdash;
                                </li>
                            @endforelse
                        </ul>
                    </td>
                    <td>{{ hyphen_nobreak($organization->getUpdatedAt()) }}</td>
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
