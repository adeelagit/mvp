<div id="users-section" class="content-section d-none">
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 fw-bold">Registered Users</h6>
            <button class="btn btn-primary btn-sm" onclick="app.modals.openUserModal()">
                <i class="fa-solid fa-plus me-1"></i> Add User
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body"></tbody>
            </table>
        </div>
    </div>
</div>