{{-- Sidebar Partial --}}
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  
  <div class="app-brand demo">
    <a href="index.html" class="app-brand-link">
      <span class="app-brand-logo demo">
<svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="#7367F0" />
  <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
  <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
  <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="#7367F0" />
</svg>
</span>
      <span class="app-brand-text demo menu-text fw-bold">GoalDocs</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <!-- Mobile Navigation Helper -->
  <div class="d-lg-none p-3 border-bottom">
    <div class="d-flex align-items-center justify-content-between">
      <span class="text-muted small">Quick Actions</span>
      <button class="btn btn-sm btn-primary" onclick="document.getElementById('file-upload').click()">
        <i class="ti ti-upload ti-xs"></i>
      </button>
    </div>
  </div>
  
  
  <ul class="menu-inner py-1">
    <!-- Dashboards -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Dashboards">Dashboards</div>
        <div class="badge bg-primary rounded-pill ms-auto">5</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item active">
          <a href="{{ route('dashboard') }}" class="menu-link">
            <div data-i18n="Activity Dashboard">Activity Dashboard</div>
          </a>
        </li>
      </ul>
    </li>


    <!-- Files Menu -->
    <li class="menu-item">
      <a href="{{ route('files.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-files"></i>
        <div data-i18n="Files">Files</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="{{ route('search.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-search"></i>
        <div data-i18n="Search">Search & Organize</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link" onclick="showMyShares()">
        <i class="menu-icon tf-icons ti ti-share"></i>
        <div data-i18n="Shares">Shares</div>
      </a>
    </li>

    <!-- Apps & Pages -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text" data-i18n="Setups">Setups</span>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-file"></i>
        @php
          $type = strtolower(auth()->user()->type ?? '');
          $typeLabels = [
            'organisation' => 'Organisation',
            'government' => 'Agency',
            'family' => 'Family',
            'social_group' => 'Community',
            'professional_group' => 'Professionals',
            'educational_institution' => 'Institution',
            'non_profit' => 'Non-profit',
            'individual' => 'Personal',
            '' => 'Account',
          ];
          $label = $typeLabels[$type] ?? ucfirst($type);
        @endphp
        <div data-i18n="{{ $label }}">{{ $label }}</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="User Profile">Profile</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="{{ route('profile') }}" class="menu-link">
                <div data-i18n="Profile">Profile</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-profile-teams.html" class="menu-link">
                <div data-i18n="Teams">Teams</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-profile-projects.html" class="menu-link">
                <div data-i18n="Projects">Projects</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-profile-connections.html" class="menu-link">
                <div data-i18n="Connections">Connections</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Account Settings">Account Settings</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="{{ route('account.settings') }}" class="menu-link">
                <div data-i18n="Account">Account</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="{{ route('account.security') }}" class="menu-link">
                <div data-i18n="Security">Security</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="{{ route('account.billing') }}" class="menu-link">
                <div data-i18n="Billing & Plans">Billing & Plans</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="{{ route('account.notifications') }}" class="menu-link">
                <div data-i18n="Notifications">Notifications</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="{{ route('account.connections') }}" class="menu-link">
                <div data-i18n="Connections">Connections</div>
              </a>
            </li>
          </ul>
        </li>
        @if(auth()->user()->is_admin)
        <li class="menu-item">
          <a href="{{ route('hierarchy.index') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-sitemap"></i>
            @php
              $type = strtolower(auth()->user()->type ?? '');
              $typeLabels = [
                'organisation' => 'Departments',
                'family' => 'Roles',
                'government' => 'Agencies',
                'social_group' => 'Groups',
                'professional_group' => 'Divisions',
                'educational_institution' => 'Departments',
                'non_profit' => 'Departments',
                'individual' => 'Categories',
                '' => 'Structure',
              ];
              $label = $typeLabels[$type] ?? 'Structure';
            @endphp
            <div data-i18n="{{ $label }}">{{ $label }}</div>
          </a>
        </li>
        @if(auth()->user()->type !== 'individual')
        <li class="menu-item">
          <a href="{{ route('users.index') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-users"></i>
            <div data-i18n="Users">Users</div>
          </a>
        </li>
        @endif
        @endif
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Misc">Misc</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="pages-misc-error.html" class="menu-link" target="_blank">
                <div data-i18n="Error">Error</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-misc-under-maintenance.html" class="menu-link" target="_blank">
                <div data-i18n="Under Maintenance">Under Maintenance</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-misc-comingsoon.html" class="menu-link" target="_blank">
                <div data-i18n="Coming Soon">Coming Soon</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-misc-not-authorized.html" class="menu-link" target="_blank">
                <div data-i18n="Not Authorized">Not Authorized</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </li>
  </ul>
  
  

</aside> 