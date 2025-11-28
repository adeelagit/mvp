<header class="top-header">
    <div class="d-flex align-items-center">
        <button class="btn btn-link d-lg-none me-3 text-dark" id="sidebarToggle">
            <i class="fa-solid fa-bars fs-5"></i>
        </button>
        <h5 class="m-0 fw-bold" id="pageTitle">Dashboard</h5>
    </div>
    
    <div class="d-flex align-items-center gap-4">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=0d6efd&color=fff" class="avatar me-2" alt="Admin">
                <div class="d-none d-sm-block">
                    <small class="d-block fw-bold text-dark">Admin User</small>
                    <small class="d-block text-muted" style="font-size: 11px;">Super Admin</small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><a class="dropdown-item" href="#" onclick="app.navigate('settings', document.querySelectorAll('.nav-link')[7])">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" onclick="app.logout()">Logout</a></li>
            </ul>
        </div>
    </div>
</header>