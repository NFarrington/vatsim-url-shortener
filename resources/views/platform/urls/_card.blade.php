<div class="card">
    <div class="card-header">
        <span class="lead">URLs</span>
        <ul class="nav nav-tabs card-header-tabs float-right">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.urls.index') ? ' active' : '' }}"
                   href="{{ route('platform.urls.index') }}">
                    List URLs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.urls.create') ? ' active' : '' }}"
                   href="{{ route('platform.urls.create') }}">
                    Create New
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
