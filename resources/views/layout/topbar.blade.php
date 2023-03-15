<!--Start topbar header-->
<header class="topbar-nav">
    <nav class="navbar navbar-expand fixed-top">
        <ul class="navbar-nav mr-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link toggle-menu" href="javascript:void();">
                    <i class="icon-menu menu-icon"></i>
                </a>
            </li>
            {{-- <li class="nav-item">
                <form class="search-bar">
                    <input type="text" class="form-control" placeholder="Enter keywords">
                    <a href="javascript:void();"><i class="icon-magnifier"></i></a>
                </form>
            </li> --}}
        </ul>

        <ul class="navbar-nav align-items-center right-nav-link">
            {{-- <li class="nav-item dropdown-lg">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret waves-effect" data-toggle="dropdown"
                    href="javascript:void();">
                    <i class="fa fa-envelope-open-o"></i></a>
            </li>
            <li class="nav-item dropdown-lg">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret waves-effect" data-toggle="dropdown"
                    href="javascript:void();">
                    <i class="fa fa-bell-o"></i></a>
            </li>
            --}}
            @if (auth()->user())
            <li class="nav-item notif">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret waves-effect" data-toggle="dropdown"
                    href="javascript:void();"><i class="fa fa-inbox"></i></a>
                <ol class="dropdown-menu dropdown-menu-right mr-4 bg-white ">
                    @foreach ($notifications as $item)
                    @if ($item->to === auth()->user()->id)
                    @if ($loop->index > 6)
                    @break
                    @endif
                    <a href="#">
                        <li
                            class="dropdown-item py-1 pl-3 {{ $item->is_read ? 'text-dark' : 'text-info font-weight-bold'}}">
                            {{$item->message}}</li>
                    </a>
                    @endif
                    @endforeach
                    <a href="#">
                        <li class="pl-2 mt-2 text-dark"><u>Lihat semua</u></li>
                    </a>
                </ol>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown" href="#">
                    <span class="user-profile"><img
                            src="{{ auth()->user()->photo ? Storage::url(auth()->user()->photo) : 'https://via.placeholder.com/110x110' }}"
                            class="img-circle" alt="user avatar"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right  mr-2">
                    <li class="dropdown-item user-details">
                        <a href="javaScript:void();">
                            <div class="media">
                                <div class="avatar"><img class="align-self-start mr-3"
                                        src="{{ auth()->user()->photo ? Storage::url(auth()->user()->photo) : 'https://via.placeholder.com/110x110' }}"
                                        alt="user avatar"></div>
                                <div class="media-body">
                                    <h6 class="mt-2 user-title">{{ auth()->user()->name }}</h6>
                                    <p class="user-subtitle">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    {{-- <li class="dropdown-item"><i class="icon-envelope mr-2"></i> Inbox</li>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-item"><i class="icon-wallet mr-2"></i> Account</li>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-item"><i class="icon-settings mr-2"></i> Setting</li>
                    <li class="dropdown-divider"></li> --}}
                    <a href="{{ route('logout') }}">
                        <li class="dropdown-item"><i class="icon-power mr-2"></i> Logout</li>
                    </a>
                </ul>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown" href="#">
                    <span class="user-profile"><img src="https://via.placeholder.com/110x110" class="img-circle"
                            alt="user avatar"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item user-details">
                        <a href="javaScript:void();">
                            <div class="media">
                                <div class="avatar"><img class="align-self-start mr-3"
                                        src="https://via.placeholder.com/110x110" alt="user avatar"></div>
                                <div class="media-body">
                                    <h6 class="mt-2 user-title">Guest</h6>
                                    <p class="user-subtitle">-</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <a href="{{ route('login') }}">
                        <li class="dropdown-item"><i class="icon-power mr-2"></i> Login</li>
                    </a>
                </ul>
            </li>
            @endif
        </ul>
    </nav>
</header>
<!--End topbar header-->