<div class="card mb-4">
    <div class="card-header">
        <span class="lead">Organization Prefix</span>
        <ul class="nav nav-tabs card-header-tabs float-right">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.organizations.index') ? ' active' : '' }}"
                   href="{{ route('platform.organizations.index') }}">
                    My Organizations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.organizations.create') ? ' active' : '' }}"
                   href="{{ route('platform.organizations.create') }}">
                    Create New
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
