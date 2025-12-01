<aside class="sidebar" id="sidebar">
    <div class="brand-logo">
        <i class="fa-solid fa-bolt"></i>
        <span>OneCharge</span>
    </div>
    
    <div class="nav-links">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" onclick="app.navigate('dashboard', this)">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('tickets', this)">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Service Tickets</span>
                </a>
            </li>

            <div class="nav-category">Management</div>

            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('users', this)">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('vehicles', this)">
                    <i class="fa-solid fa-car-side"></i>
                    <span>Vehicles</span>
                </a>
            </li>
            
            <div class="nav-category">Master Data</div>

            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('types', this)">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Vehicle Types</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('brands', this)">
                    <i class="fa-solid fa-tags"></i>
                    <span>Brands & Models</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('plates', this)">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Number Plates</span>
                </a>
            </li>

            {{-- <div class="nav-category">System</div>

            <li class="nav-item">
                <a class="nav-link" onclick="app.navigate('settings', this)">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </li> --}}

            <li class="nav-item">
                <a onclick="app.logout()" class="nav-link text-danger">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>