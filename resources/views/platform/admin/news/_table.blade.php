@if($news->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Published</th>
                <th>Created</th>
                <th>Updated</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($news as $post)
                <tr>
                    <td>{{ $post->getId() }}</td>
                    <td>{{ $post->getTitle() }}</td>
                    <td><i class="material-icons">{{ $post->isPublished() ? 'check' : 'close' }}</i></td>
                    <td>{{ hyphen_nobreak($post->getCreatedAt()) }}</td>
                    <td>{{ hyphen_nobreak($post->getUpdatedAt()) }}</td>
                    <td><a href="{{ route('platform.admin.news.edit', $post) }}">Edit</a></td>
                    <td>
                        <delete-resource link-only route="{{ route('platform.admin.news.destroy', $post) }}">
                        </delete-resource>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-auto">{{ $news->links() }}</div>
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif
