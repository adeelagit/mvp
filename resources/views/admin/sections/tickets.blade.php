<div id="tickets-section" class="content-section d-none">
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 fw-bold">Service Management</h6>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" style="width: 150px;">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="assigned">Assigned</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Info</th>
                        <th>Issue</th>
                        <th>Location</th>
                        <th>Media</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tickets-table-body"></tbody>
            </table>
        </div>
    </div>
</div>