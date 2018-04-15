@if($urls->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>URL</th>
                <th>Redirect</th>
                <th>Created</th>
                <th>Updated</th>
                <th></th>
            </tr>
            @foreach($urls as $url)
                <tr>
                    <td>{{ $url->id }}</td>
                    <td class="break-all"><a href="{{ url($url->url) }}">{{ $url->url }}</a></td>
                    <td class="break-all"><a href="{{ $url->redirect_url }}">{{ $url->redirect_url }}</a></td>
                    <td>{{ hyphen_nobreak($url->created_at) }}</td>
                    <td>{{ hyphen_nobreak($url->updated_at) }}</td>
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
