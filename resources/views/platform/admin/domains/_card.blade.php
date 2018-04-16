<div class="card">
    <div class="card-header">
        <span class="lead">Domains</span>
        <ul class="nav nav-tabs card-header-tabs float-right">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.domains.index') ? ' active' : '' }}"
                   href="{{ route('platform.admin.domains.index') }}">
                    List Domains
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.domains.create') ? ' active' : '' }}"
                   href="{{ route('platform.admin.domains.create') }}">
                    Create New
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
