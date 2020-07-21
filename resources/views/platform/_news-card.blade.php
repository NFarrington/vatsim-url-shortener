<div class="card mb-4">
    <div class="card-header">
        <span class="lead">{{ $post->getTitle() }}</span>
        <small class="text-muted" data-toggle="tooltip" data-placement="top"
               title="{{ $post->getCreatedAt()->format('Y-m-d H:i:s T') }}">
            Posted {{ $post->getCreatedAt()->diffForHumansAt() }}
        </small>
    </div>
    <div class="card-body">
        {!! \Markdown::convertToHtml($post->getContent()) !!}
    </div>
</div>
