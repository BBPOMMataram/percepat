<!--Start sidebar-wrapper-->
<div id="sidebar-wrapper" data-simplebar="" data-simplebar-auto-hide="true">
    <div class="brand-logo">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('vendor/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon">
            <h5 class="logo-text">{{ env('APP_NAME') }}</h5>
        </a>
    </div>
    <ul class="sidebar-menu do-nicescrol">
        <li class="sidebar-header">MAIN NAVIGATION</li>
        <li>
            <a href="{{ route('dashboard') }}">
                <i class="zmdi zmdi-view-dashboard"></i> <span>Dashboard</span>
            </a>
        </li>

        @if (auth()->user()->level === 'admin')
        <li>
            <a href="{{ route('users.index') }}">
                <i class="zmdi zmdi-accounts"></i> <span>Users</span>
            </a>
        </li>
        @endif

        <li>
            <a href="{{ route('pembelian.index') }}">
                <i class="zmdi zmdi-shopping-cart-plus"></i> <span>Pembelian</span>
            </a>
        </li>

        <li>
            <a href="{{ route('permintaan.index') }}">
                <i class="zmdi zmdi-assignment"></i> <span>Permintaan</span>
            </a>
        </li>
        <li class="sidebar-header">MASTER</li>

        <li>
            <a href="{{ route('barang.index') }}">
                <i class="zmdi zmdi-cocktail"></i> <span>Barang</span>
            </a>
        </li>

        <li>
            <a href="{{ route('bidang.index') }}">
                <i class="zmdi zmdi-balance"></i> <span>Bidang</span>
            </a>
        </li>

        <li class="sidebar-header">SETTNGS</li>

        <li>
            <a href="{{ route('profile.index') }}">
                <i class="zmdi zmdi-face"></i> <span>Profile</span>
            </a>
        </li>

    </ul>

</div>
<!--End sidebar-wrapper-->