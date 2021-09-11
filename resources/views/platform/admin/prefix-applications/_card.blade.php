<div class="card">
    <div class="card-header">
        <span class="lead">Prefix Applications</span>
        <ul class="nav nav-tabs card-header-tabs float-right">
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('platform.admin.prefix-applications.index') ? ' active' : '' }}"
                   href="{{ route('platform.admin.prefix-applications.index') }}">
                    List Prefix Applications
                </a>
            </li>
        </ul>
    </div>

    {{ $slot }}
</div>
