@if($urls->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr class="link-unstyled">
                <th>@sortablelink('id', 'ID', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('fullUrl', 'URL', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('redirectUrl', 'Redirect URL', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('owner', 'Owner', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('updatedAt', 'Last Updated', null, ['class' => 'text-nowrap'])</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($urls as $url)
                <tr>
                    <td>{{ $url->getId() }}</td>
                    <td class="break-all"><a href="{{ url($url->getFullUrl()) }}">{{ preg_replace('#^https?://#', '', $url->getFullUrl()) }}</a></td>
                    <td class="break-all"><a href="{{ $url->getRedirectUrl() }}">{{ preg_replace('#^https?://#', '', $url->getRedirectUrl()) }}</a></td>
                    <td>
                        @if($url->getOrganization())
                            <a href="{{ route('platform.admin.organizations.show', $url->getOrganization()) }}">{{ $url->getOrganization()->getName() }}</a>
                        @elseif($url->getUser())
                            {{ $url->getUser()->getDisplayInfo() }}
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td>{{ hyphen_nobreak($url->getUpdatedAt()) }}</td>
                    <td><a href="{{ route('platform.admin.urls.edit', $url) }}">Edit</a></td>
                    <td>
                        <delete-resource link-only route="{{ route('platform.admin.urls.destroy', $url) }}"
                                         message="Are you sure you want to delete this URL?"></delete-resource>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-auto">{{ $urls->withQueryString()->links() }}</div>
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
