<nav class="mb-4">
    <ol class="breadcrumb">
        @foreach(breadcrumbs_array() as $segment)
            <li class="breadcrumb-item{{ $loop->last ? ' active' : '' }}">
                @if($loop->last)
                    {{ $segment['name'] }}
                @else
                    <a href="{{ url($segment['path']) }}">{{ $segment['name'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
