<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <a class="navbar-brand" href="{{ route('site.home') }}">{{ config('app.name') }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            @auth
                <li class="nav-item{{ Request::routeIs('platform.dashboard') ? ' active' : '' }}">
                    <a class="nav-link" href="{{ route('platform.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item{{ Request::routeIs('platform.urls.*') ? ' active' : '' }}">
                    <a class="nav-link" href="{{ route('platform.urls.index') }}">URLs</a>
                </li>
                <li class="nav-item{{ Request::routeIs('platform.settings*') ? ' active' : '' }}">
                    <a class="nav-link" href="{{ route('platform.settings') }}">Settings</a>
                </li>
                @can('admin')
                    <li class="nav-item dropdown{{ Request::routeIs('platform.admin.*') ? ' active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Admin</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item{{ Request::routeIs('platform.admin.news.*') ? ' active' : '' }}"
                               href="{{ route('platform.admin.news.index') }}">News</a>
                        </div>
                    </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('site.home') ? ' active' : '' }}"
                       href="{{ route('site.home') }}">Return to Main Site</a>
                </li>
                <li class="nav-item{{ Request::routeIs('login') ? ' active' : '' }}">
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
            @endauth
        </ul>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>

        {{--@auth--}}
        {{--<form class="form-inline mt-2 mt-md-0">--}}
        {{--<input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">--}}
        {{--<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>--}}
        {{--</form>--}}
        {{--@endauth--}}
    </div>
</nav>
