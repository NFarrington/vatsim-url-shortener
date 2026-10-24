<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('site.home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('platform.dashboard') ? ' active' : '' }}"
                           href="{{ route('platform.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('platform.urls.*') ? ' active' : '' }}"
                           href="{{ route('platform.urls.index') }}">URLs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('platform.organizations.*') ? ' active' : '' }}"
                           href="{{ route('platform.organizations.index') }}">Organizations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('platform.settings*') ? ' active' : '' }}"
                           href="{{ route('platform.settings') }}">Settings</a>
                    </li>
                    @can('admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle{{ Request::routeIs('platform.admin.*') ? ' active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item{{ Request::routeIs('platform.admin.domains.*') ? ' active' : '' }}"
                                   href="{{ route('platform.admin.domains.index') }}">Domains</a>
                                <a class="dropdown-item{{ Request::routeIs('platform.admin.news.*') ? ' active' : '' }}"
                                   href="{{ route('platform.admin.news.index') }}">News</a>
                                <a class="dropdown-item{{ Request::routeIs('platform.admin.organizations.*') ? ' active' : '' }}"
                                   href="{{ route('platform.admin.organizations.index') }}">Organizations</a>
                                <a class="dropdown-item{{ Request::routeIs('platform.admin.prefix-applications.*') ? ' active' : '' }}"
                                   href="{{ route('platform.admin.prefix-applications.index') }}">Prefix Applications</a>
                                <a class="dropdown-item{{ Request::routeIs('platform.admin.urls.*') ? ' active' : '' }}"
                                   href="{{ route('platform.admin.urls.index') }}">URLs</a>
                            </div>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('platform.logout') }}" data-submit-form="logout-form">
                            Logout
                        </a>
                    </li>
                @else
                    @sectionMissing('no-navigation')
                        <li class="nav-item">
                            <a class="nav-link{{ Request::routeIs('site.home') ? ' active' : '' }}"
                               href="{{ route('site.home') }}">Return to Main Site</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link{{ Request::routeIs('platform.login') ? ' active' : '' }}"
                               href="{{ route('platform.login') }}">Login</a>
                        </li>
                    @endif
                @endauth
            </ul>
            <form id="logout-form" action="{{ route('platform.logout') }}" method="POST" class="d-none">
                {{ csrf_field() }}
            </form>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('platform.support') ? ' active' : '' }}"
                       href="{{ route('platform.support') }}">Help &amp; Support</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
