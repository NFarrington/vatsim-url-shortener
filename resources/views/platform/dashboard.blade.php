@extends('platform.layout')

@section('content')
    @if($news->isNotEmpty())
        @foreach($news as $post)
            <div class="card mb-4">
                <div class="card-header">
                    <span class="lead">{{ $post->title }}</span>
                    <small class="text-muted" data-toggle="tooltip" data-placement="top" title="{{ $post->created_at->format('Y-m-d H:i:s T') }}">
                        Posted {{ $post->created_at->diffForHumansAt() }}
                    </small>
                </div>
                <div class="card-body">
                    {!! \Markdown::convertToHtml($post->content) !!}
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center">{{ $news->links() }}</div>
    @else
        <div class="card">
            <div class="card-body">
                <p class="card-text">You are logged in!</p>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endpush
