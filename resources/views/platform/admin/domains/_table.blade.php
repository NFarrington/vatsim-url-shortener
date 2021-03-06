@if($domains->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>URL</th>
                <th>Public</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($domains as $domain)
                <tr>
                    <td>{{ $domain->getId() }}</td>
                    <td><a href="{{ $domain->getUrl() }}">{{ $domain->getUrl() }}</a></td>
                    <td><i class="material-icons">{{ $domain->isPublic() ? 'check' : 'close' }}</i></td>
                    <td><a href="{{ route('platform.admin.domains.edit', $domain) }}">Edit</a></td>
                    <td>
                        <delete-resource link-only route="{{ route('platform.admin.domains.destroy', $domain) }}">
                        </delete-resource>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-auto">{{ $domains->links() }}</div>
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
