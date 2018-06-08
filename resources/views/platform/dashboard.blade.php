@extends('platform.layout')

@section('content')
    @if($news->isNotEmpty())
        @foreach($news as $post)
            @include('platform._news-card')
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
