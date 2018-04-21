@if($urls->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>URL</th>
                <th>Redirect</th>
                @if($user->organizations->isNotEmpty())
                    <th>Organization</th>
                @endif
                <th>Created</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($urls as $url)
                <tr>
                    <td>{{ $url->id }}</td>
                    <td class="break-all"><a href="{{ url($url->full_url) }}">{{ $url->full_url }}</a></td>
                    <td class="break-all"><a href="{{ $url->redirect_url }}">{{ $url->redirect_url }}</a></td>
                    @if($user->organizations->isNotEmpty())
                        <td>{{ $url->organization->name ?? new \Illuminate\Support\HtmlString('&mdash;') }}</td>
                    @endif
                    <td>{{ hyphen_nobreak($url->created_at) }}</td>
                    <td><a href="{{ route('platform.urls.edit', $url) }}">Edit</a></td>
                    <td>
                        <delete-resource link-only route="{{ route('platform.urls.destroy', $url) }}"></delete-resource>
                    </td>
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
