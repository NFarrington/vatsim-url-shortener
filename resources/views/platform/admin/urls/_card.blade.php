<div class="card">
    <div class="card-header">
        <span class="lead">URLs</span>
        <ul class="nav nav-tabs card-header-tabs float-end">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.urls.index') ? ' active' : '' }}"
                   href="{{ route('platform.admin.urls.index') }}">
                    List URLs
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
