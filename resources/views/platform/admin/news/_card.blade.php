<div class="card mb-4">
    <div class="card-header">
        <span class="lead">News</span>
        <ul class="nav nav-tabs card-header-tabs float-right">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.news.index') ? ' active' : '' }}"
                   href="{{ route('platform.admin.news.index') }}">
                    List News
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.news.create') ? ' active' : '' }}"
                   href="{{ route('platform.admin.news.create') }}">
                    Create New
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
