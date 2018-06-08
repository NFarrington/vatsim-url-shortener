<div class="card mb-4">
    <div class="card-header">
        <span class="lead">{{ $post->title }}</span>
        <small class="text-muted" data-toggle="tooltip" data-placement="top"
               title="{{ $post->created_at->format('Y-m-d H:i:s T') }}">
            Posted {{ $post->created_at->diffForHumansAt() }}
        </small>
    </div>
    <div class="card-body">
        {!! \Markdown::convertToHtml($post->content) !!}
    </div>
</div>
