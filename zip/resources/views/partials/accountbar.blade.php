{{-- Sidebar Partial --}}
<ul class="nav nav-pills flex-column flex-md-row mb-4">
    <li class="nav-item">
        <a class="nav-link{{ Route::is('account.settings') ? ' active' : '' }}" href="{{ route('account.settings') }}">
            <i class="ti-xs ti ti-users me-1"></i> Account
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ Route::is('account.security') ? ' active' : '' }}" href="{{ route('account.security') }}">
            <i class="ti-xs ti ti-lock me-1"></i> Security
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ Route::is('account.billing') ? ' active' : '' }}" href="{{ route('account.billing') }}">
            <i class="ti-xs ti ti-file-description me-1"></i> Billing & Plans
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ Route::is('account.notifications') ? ' active' : '' }}" href="{{ route('account.notifications') }}">
            <i class="ti-xs ti ti-bell me-1"></i> Notifications
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{{ Route::is('account.connections') ? ' active' : '' }}" href="{{ route('account.connections') }}">
            <i class="ti-xs ti ti-link me-1"></i> Connections
        </a>
    </li>
</ul>