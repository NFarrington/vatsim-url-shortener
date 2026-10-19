<div class="card">
    <div class="card-header">
        <span class="lead">Organizations</span>
        <ul class="nav nav-tabs card-header-tabs float-right">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.organizations.index') ? ' active' : '' }}"
                   href="{{ route('platform.admin.organizations.index') }}">
                    List Organizations
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
