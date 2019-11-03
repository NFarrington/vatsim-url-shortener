@if($urls->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr class="link-unstyled">
                <th>@sortablelink('id', 'ID', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('url', 'URL', null, ['class' => 'text-nowrap'])</th>
                <th>@sortablelink('redirect_url', 'Redirect URL', null, ['class' => 'text-nowrap'])</th>
                @if($user->organizations->isNotEmpty())
                    <th>@sortablelink('organization.name', 'Organization', null, ['class' => 'text-nowrap'])</th>
                @endif
                <th>@sortablelink('updated_at', 'Last Updated', null, ['class' => 'text-nowrap'])</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($urls as $url)
                <tr>
                    <td>{{ $url->id }}</td>
                    <td class="break-all"><a href="{{ url($url->full_url) }}">{{ preg_replace('#^https?://#', '', $url->full_url) }}</a></td>
                    <td class="break-all"><a href="{{ $url->redirect_url }}">{{ preg_replace('#^https?://#', '', $url->redirect_url) }}</a></td>
                    @if($user->organizations->isNotEmpty())
                        <td>{{ $url->organization->name ?? new \Illuminate\Support\HtmlString('&mdash;') }}</td>
                    @endif
                    <td>{{ hyphen_nobreak($url->updated_at) }}</td>
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
