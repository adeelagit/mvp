<div id="settings-section" class="content-section d-none">
    <div class="row">
        <div class="col-md-6">
            <div class="content-card">
                <h6 class="mb-4 fw-bold">Change Password</h6>
                <form onsubmit="event.preventDefault(); alert('Feature disabled in demo');">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="content-card">
                <h6 class="mb-4 fw-bold">App Configuration</h6>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="maintenanceMode">
                    <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="allowReg" checked>
                    <label class="form-check-label" for="allowReg">Allow User Registration</label>
                </div>
            </div>
        </div>
    </div>
</div>