<div id="dashboard-section" class="content-section">
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-sm mb-0 text-muted font-weight-bold">Total Users</p>
                    <h4 class="font-weight-bolder mb-0" id="stat-users">0</h4>
                </div>
                <div class="stat-icon bg-gradient-primary shadow text-center text-white">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-sm mb-0 text-muted font-weight-bold">Registered EVs</p>
                    <h4 class="font-weight-bolder mb-0" id="stat-vehicles">0</h4>
                </div>
                <div class="stat-icon bg-gradient-success shadow text-center text-white">
                    <i class="fa-solid fa-car"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-sm mb-0 text-muted font-weight-bold">Active Tickets</p>
                    <h4 class="font-weight-bolder mb-0" id="stat-tickets">0</h4>
                </div>
                <div class="stat-icon bg-gradient-warning shadow text-center text-white">
                    <i class="fa-solid fa-ticket"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-sm mb-0 text-muted font-weight-bold">Brands</p>
                    <h4 class="font-weight-bolder mb-0" id="stat-brands">0</h4>
                </div>
                <div class="stat-icon bg-gradient-info shadow text-center text-white">
                    <i class="fa-solid fa-tags"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 fw-bold">Recent Service Requests</h6>
            <button class="btn btn-sm btn-outline-primary" onclick="app.navigate('tickets', document.querySelectorAll('.nav-link')[1])">View All</button>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>User</th>
                        <th>Issue Category</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="dashboard-tickets-body"></tbody>
            </table>
        </div>
    </div>
</div>