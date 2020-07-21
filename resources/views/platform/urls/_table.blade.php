@if($urls->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr class="link-unstyled">
                <th>@sortablelink('id', 'ID', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('fullUrl', 'URL', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('redirectUrl', 'Redirect URL', null, ['class' => 'text-nowrap'])</th>
                @if(!empty($user->getUserOrganizations()))
                    <th>@sortablelink('organization.name', 'Organization', null, ['class' => 'text-nowrap'])</th>
                @endif
                <th>@sortablelink('updatedAt', 'Last Updated', null, ['class' => 'text-nowrap'])</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($urls as $url)
                <tr>
                    <td>{{ $url->getId() }}</td>
                    <td class="break-all"><a href="{{ url($url->getFullUrl()) }}">{{ preg_replace('#^https?://#', '', $url->getFullUrl()) }}</a></td>
                    <td class="break-all"><a href="{{ $url->getRedirectUrl() }}">{{ preg_replace('#^https?://#', '', $url->getRedirectUrl()) }}</a></td>
                    @if(!empty($user->getUserOrganizations()))
                        <td>{{ $url->getOrganization() ? $url->getOrganization()->getName() : new \Illuminate\Support\HtmlString('&mdash;') }}</td>
                    @endif
                    <td>{{ hyphen_nobreak($url->getUpdatedAt()) }}</td>
                    <td>
                        @can('update', $url)
                            <a href="{{ route('platform.urls.edit', $url) }}">Edit</a>
                        @else
                            <s class="text-muted">Delete</s>
                        @endcan
                    </td>
                    <td>
                        @can('delete', $url)
                            <delete-resource link-only
                                             route="{{ route('platform.urls.destroy', $url) }}"></delete-resource>
                        @else
                            <s class="text-muted">Delete</s>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    @if(!Request::has('sort'))
        <div class="mx-auto">{{ $urls->links() }}</div>
    @else
        <div class="mx-auto">
            {{ $urls->appends(['sort' => Request::get('sort'), 'direction' => Request::get('direction')])->links() }}
        </div>
    @endif
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
